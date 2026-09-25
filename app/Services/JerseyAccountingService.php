<?php

namespace App\Services;

use App\Models\JerseyOrder;
use App\Models\JerseyOrderPayment;
use Illuminate\Support\Facades\DB;

/**
 * Menjembatani Order Jersey ke akuntansi (sebelumnya modul Jersey berdiri
 * sendiri tanpa jurnal sama sekali).
 *
 * - Pendapatan: total order diakui (Dr Piutang / Cr Pendapatan Order Jersey
 *   4300) dan dikoreksi lewat SELISIH tiap total berubah, dilacak di
 *   jersey_orders.total_terjurnal - jadi tetap benar walau line order
 *   ditambah/diedit/dihapus berkali-kali.
 * - Pembayaran: tiap pembayaran menjurnal (Dr Kas/Bank / Cr Piutang) dengan
 *   referensi unik "{order}/BYR-{id}" supaya bisa di-void sendiri.
 * - Void pembayaran & pembatalan order hanya lewat persetujuan (lihat
 *   ApprovalController).
 */
class JerseyAccountingService
{
    public static function sinkronPendapatan(JerseyOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $row = DB::table('jersey_orders')->where('id', $order->id)->lockForUpdate()->first();
            $selisih = round((float) $row->total - (float) $row->total_terjurnal, 2);

            if (abs($selisih) < 0.01) {
                return;
            }

            AccountingService::postPendapatanJersey($row->order_number, $selisih);
            DB::table('jersey_orders')->where('id', $order->id)->update(['total_terjurnal' => $row->total]);
        });
    }

    public static function catatPembayaran(JerseyOrder $order, JerseyOrderPayment $payment): void
    {
        $ref = "{$order->order_number}/BYR-{$payment->id}";

        AccountingService::postPembayaranJersey($ref, $order->order_number, (float) $payment->jumlah, $payment->metode_bayar);

        DB::table('jersey_order_payments')->where('id', $payment->id)->update(['no_referensi_jurnal' => $ref]);

        // Cermin di buku Kas Kecil (pemasukan sumber 'jersey'); jurnalnya sudah dibuat di atas, jadi tidak dijurnal lagi.
        DB::table('kas_kecil')->insert([
            'tanggal' => $payment->tanggal_bayar, 'jenis' => 'Masuk', 'sumber' => 'jersey', 'id_referensi' => $payment->id, 'metode' => $payment->metode_bayar,
            'id_kategori' => null, 'keterangan' => "Pembayaran order jersey {$order->order_number}", 'nominal' => $payment->jumlah,
            'id_user' => $payment->created_by ?? \Illuminate\Support\Facades\Auth::id(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Dipanggil lewat approval 'void_pembayaran_jersey'. */
    public static function voidPembayaran(int $idPembayaran, string $alasan): void
    {
        DB::transaction(function () use ($idPembayaran, $alasan) {
            $payment = JerseyOrderPayment::whereKey($idPembayaran)->lockForUpdate()->first();

            if (!$payment) {
                throw new \RuntimeException('Pembayaran jersey tidak ditemukan atau sudah dihapus.');
            }

            // Pembayaran lama (sebelum integrasi akuntansi) tidak punya
            // referensi jurnal - tidak ada yang perlu dibalik.
            if ($payment->no_referensi_jurnal) {
                AccountingService::reverseByReferensi($payment->no_referensi_jurnal);
            }

            $order = $payment->order;
            $payment->update(['keterangan' => trim(($payment->keterangan ? $payment->keterangan . ' | ' : '') . 'Di-void: ' . $alasan)]);
            $payment->delete();
            DB::table('kas_kecil')->where('sumber', 'jersey')->where('id_referensi', $idPembayaran)->delete();
            $order->recalculatePembayaran();
        });
    }

    /** Dipanggil lewat approval 'batalkan_order_jersey'. */
    public static function batalkanOrder(int $idOrder, string $alasan): void
    {
        DB::transaction(function () use ($idOrder) {
            $order = JerseyOrder::whereKey($idOrder)->lockForUpdate()->first();

            if (!$order) {
                throw new \RuntimeException('Order jersey tidak ditemukan atau sudah dihapus.');
            }

            if ($order->payments()->exists()) {
                throw new \RuntimeException("Order {$order->order_number} masih punya pembayaran - void pembayarannya dulu.");
            }

            AccountingService::postPendapatanJersey($order->order_number, -1 * (float) $order->total_terjurnal);
            JerseyStokService::kembalikan($order); // stok kain kembali bila sudah dipotong

            $order->delete();
        });
    }
}

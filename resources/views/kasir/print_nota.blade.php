<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota {{ $transaksi->nomor_nota }}</title>
    @include('partials.print-style')
    <style>
        /* Nama item DIPOTONG satu baris (ellipsis), BUKAN dibiarkan
           membungkus ke banyak baris - supaya nota tidak "memanjang" kalau
           banyak item dengan nama panjang (permintaan eksplisit user).
           Keterangan ukuran (kalau ada) tampil di baris kecil terpisah di
           bawahnya, bukan ikut memperpanjang baris nama. */
        .item-nama {
            max-width: 1px; /* dipaksa table-layout mengecil sesuai sisa ruang, bukan melebar ikut isi */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .item-ukuran { font-size: 9px; color: #94a3b8; white-space: nowrap; }
        table.item-table { table-layout: fixed; }
    </style>
</head>
<body>
    <div class="sheet">
        @include('partials.print-header', ['docTitle' => 'Nota Penjualan'])

        <table>
            <tr><td>No. Nota</td><td class="right">{{ $transaksi->nomor_nota }}</td></tr>
            <tr><td>Waktu</td><td class="right">{{ \Carbon\Carbon::parse($transaksi->tgl_order)->format('d/m/Y H:i:s') }}</td></tr>
            <tr><td>Konsumen</td><td class="right">{{ $transaksi->nama_konsumen }}</td></tr>
            <tr><td>Telp</td><td class="right">{{ $transaksi->telepon ?: '-' }}</td></tr>
            <tr><td>Kasir</td><td class="right">{{ $transaksi->nama_kasir ?? '-' }}</td></tr>
        </table>

        <div class="divider"></div>

        <table class="item-table">
            <thead>
                <tr><th style="width: 15%;">Qty</th><th>Produk</th><th class="right" style="width: 30%;">Total</th></tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ rtrim(rtrim(number_format($item->qty, 2, ',', ''), '0'), ',') }}x</td>
                    <td class="item-nama" title="{{ $item->nama_file }}">
                        {{ $item->nama_file }}
                        @if($item->p_ukuran > 0 && $item->l_ukuran > 0)
                            <br><span class="item-ukuran">[{{ rtrim(rtrim(number_format($item->p_ukuran, 1, ',', ''), '0'), ',') }}x{{ rtrim(rtrim(number_format($item->l_ukuran, 1, ',', ''), '0'), ',') }} cm]</span>
                        @endif
                    </td>
                    <td class="right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        {{-- Rincian komponen biaya HANYA ditampilkan yang > 0 - kebanyakan
             order tidak pakai semua komponen sekaligus (mis. tanpa
             finishing), baris Rp 0 cuma bikin nota lebih panjang tanpa
             informasi berguna. --}}
        <table>
            @if($totalCetak > 0)
            <tr><td>Total Cetak</td><td class="right">{{ number_format($totalCetak, 0, ',', '.') }}</td></tr>
            @endif
            @if($totalDesain > 0)
            <tr><td>Total Desain</td><td class="right">{{ number_format($totalDesain, 0, ',', '.') }}</td></tr>
            @endif
            @if($totalFinishing > 0)
            <tr><td>Total Finishing</td><td class="right">{{ number_format($totalFinishing, 0, ',', '.') }}</td></tr>
            @endif
            <tr class="total-row"><td>Grand Total</td><td class="right">{{ number_format($transaksi->total_bayar, 0, ',', '.') }}</td></tr>
            <tr><td>Krg. Bayar</td><td class="right">{{ number_format($kurangBayar, 0, ',', '.') }}</td></tr>
            <tr><td>Kembali</td><td class="right">{{ number_format($kembali, 0, ',', '.') }}</td></tr>
        </table>

        {{-- Blok deposit cuma tampil kalau konsumen ini memang punya riwayat
             deposit (setor/pakai) - konsumen yang tidak pernah pakai fitur
             deposit sama sekali tidak perlu lihat baris "Saldo Rp 0" yang
             tidak relevan buat mereka. --}}
        @if($konsumenPernahPakaiDeposit)
            <div class="divider"></div>
            <table>
                <tr><td>Penggunaan deposit pada trans ini</td><td class="right">{{ number_format($depositTerpakaiTransaksiIni, 0, ',', '.') }}</td></tr>
                <tr><td>Saldo deposit anda saat ini</td><td class="right">{{ number_format($saldoDepositSaatIni, 0, ',', '.') }}</td></tr>
            </table>
        @endif

        {{-- nota_footer_text sudah disaring ketat saat disimpan (whitelist
             tag, semua atribut dibuang - lihat
             SettingAplikasiController::sanitizeFooterHtml()), aman dicetak
             sebagai HTML mentah supaya format tebal/miring/daftar yang
             diatur admin ikut tampil, bukan cuma teks polos.
             $footerNotaOverride dipakai KHUSUS oleh
             SettingAplikasiController::previewNota() (preview live tab
             Nota, sebelum disimpan) - sudah disaring sanitizer yang sama
             di sana juga sebelum sampai ke sini. --}}
        @php($footerNota = $footerNotaOverride ?? \App\Models\AppSetting::current()->nota_footer_text)
        @if($footerNota)
            <div class="divider"></div>
            <div class="footer-note" style="text-align: left;">{!! $footerNota !!}</div>
        @endif

        <p class="footer-note">===== Terima Kasih =====</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>
</body>
</html>

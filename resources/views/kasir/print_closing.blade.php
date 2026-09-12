<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Closing Kas #{{ $kas->id_kas }}</title>
    @include('partials.print-style')
</head>
<body>
    <div class="sheet">
        @include('partials.print-header', ['docTitle' => 'Laporan Closing Kasir'])

        <table>
            <tr><td>Tanggal Kas</td><td class="right">{{ \Carbon\Carbon::parse($kas->tgl_kas)->format('d/m/Y') }}</td></tr>
            <tr><td>Kasir</td><td class="right">{{ $kas->nama_kasir ?? '-' }}</td></tr>
            <tr><td>Waktu Tutup</td><td class="right">{{ $kas->waktu_tutup ? \Carbon\Carbon::parse($kas->waktu_tutup)->format('d/m/Y H:i') : '-' }}</td></tr>
        </table>

        <div class="divider"></div>

        <table>
            <tr><td>Saldo Awal (Modal)</td><td class="right">{{ number_format($kas->saldo_awal, 0, ',', '.') }}</td></tr>
            <tr><td>Pemasukan Tunai</td><td class="right">{{ number_format($kas->total_tunai_masuk, 0, ',', '.') }}</td></tr>
            <tr><td>Pemasukan Transfer</td><td class="right">{{ number_format($kas->total_transfer_masuk, 0, ',', '.') }}</td></tr>
            <tr><td>Pemasukan Debit</td><td class="right">{{ number_format($kas->total_debit_masuk ?? 0, 0, ',', '.') }}</td></tr>
            <tr><td>Pengeluaran Tunai</td><td class="right">{{ number_format($kas->total_tunai_keluar, 0, ',', '.') }}</td></tr>
        </table>

        <div class="divider"></div>

        <table>
            <tr class="total-row"><td>Saldo Akhir Sistem</td><td class="right">Rp {{ number_format($kas->saldo_akhir, 0, ',', '.') }}</td></tr>
            <tr class="total-row"><td>Saldo Fisik Laci</td><td class="right">Rp {{ number_format($kas->saldo_fisik, 0, ',', '.') }}</td></tr>
            <tr class="total-row {{ ($kas->selisih ?? 0) < 0 ? 'selisih-negatif' : (($kas->selisih ?? 0) > 0 ? 'selisih-positif' : '') }}"><td>Selisih</td><td class="right">Rp {{ number_format($kas->selisih, 0, ',', '.') }}</td></tr>
        </table>

        <div class="divider"></div>
        <table>
            <tr><td>Status Rolling</td><td class="right">{{ $kas->status_rolling ?? '-' }}</td></tr>
        </table>

        @if($kas->keterangan_closing)
        <div class="divider"></div>
        <p class="sub">Catatan: {{ $kas->keterangan_closing }}</p>
        @endif
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Closing Kas #{{ $kas->id_kas }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; margin: 0; padding: 24px; color: #111; }
        .sheet { max-width: {{ $format === 'thermal' ? '58mm' : '480px' }}; margin: 0 auto; }
        h1 { font-size: {{ $format === 'thermal' ? '13px' : '18px' }}; text-align: center; margin: 0 0 4px; }
        .center { text-align: center; }
        .sub { font-size: 11px; color: #555; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; font-size: {{ $format === 'thermal' ? '10px' : '12px' }}; }
        td { padding: 4px 2px; }
        td.right { text-align: right; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .total-row td { font-weight: bold; font-size: {{ $format === 'thermal' ? '11px' : '14px' }}; }
        .selisih { color: {{ ($kas->selisih ?? 0) < 0 ? '#c0392b' : (($kas->selisih ?? 0) > 0 ? '#2c7a3f' : '#111') }}; }
        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { padding: 10px 24px; font-family: sans-serif; font-weight: bold; cursor: pointer; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <h1>PRINT-ERP</h1>
        <p class="center sub">Laporan Closing Kasir</p>

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
            <tr><td>Pengeluaran Tunai</td><td class="right">{{ number_format($kas->total_tunai_keluar, 0, ',', '.') }}</td></tr>
        </table>

        <div class="divider"></div>

        <table>
            <tr class="total-row"><td>Saldo Akhir Sistem</td><td class="right">Rp {{ number_format($kas->saldo_akhir, 0, ',', '.') }}</td></tr>
            <tr class="total-row"><td>Saldo Fisik Laci</td><td class="right">Rp {{ number_format($kas->saldo_fisik, 0, ',', '.') }}</td></tr>
            <tr class="total-row selisih"><td>Selisih</td><td class="right">Rp {{ number_format($kas->selisih, 0, ',', '.') }}</td></tr>
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

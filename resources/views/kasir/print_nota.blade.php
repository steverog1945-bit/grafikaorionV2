<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota {{ $transaksi->nomor_nota }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; margin: 0; padding: 24px; color: #111; }
        .sheet { max-width: {{ $format === 'thermal' ? '58mm' : '480px' }}; margin: 0 auto; }
        h1 { font-size: {{ $format === 'thermal' ? '13px' : '18px' }}; text-align: center; margin: 0 0 4px; }
        .center { text-align: center; }
        .sub { font-size: 11px; color: #555; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; font-size: {{ $format === 'thermal' ? '10px' : '12px' }}; }
        th, td { padding: 4px 2px; text-align: left; }
        th { border-bottom: 1px dashed #000; }
        td.right, th.right { text-align: right; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .total-row td { font-weight: bold; font-size: {{ $format === 'thermal' ? '11px' : '14px' }}; }
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
        <p class="center sub">Nota Penjualan</p>

        <table>
            <tr><td>No. Nota</td><td class="right">{{ $transaksi->nomor_nota }}</td></tr>
            <tr><td>Tanggal</td><td class="right">{{ \Carbon\Carbon::parse($transaksi->tgl_order)->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Konsumen</td><td class="right">{{ $transaksi->nama_konsumen }}</td></tr>
            <tr><td>Kasir</td><td class="right">{{ $transaksi->nama_kasir ?? '-' }}</td></tr>
        </table>

        <div class="divider"></div>

        <table>
            <thead>
                <tr><th>Item</th><th class="right">Qty</th><th class="right">Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->nama_file }}</td>
                    <td class="right">{{ $item->qty }}</td>
                    <td class="right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table>
            <tr class="total-row"><td>TOTAL</td><td class="right" colspan="2">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</td></tr>
            <tr><td>Terbayar</td><td class="right" colspan="2">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td></tr>
            <tr><td>Status</td><td class="right" colspan="2">{{ $transaksi->status_pembayaran }}</td></tr>
        </table>

        <p class="center sub" style="margin-top:16px;">Terima kasih atas kepercayaan Anda</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>
</body>
</html>

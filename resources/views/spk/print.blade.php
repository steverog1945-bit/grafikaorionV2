<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK #{{ $detail->id_detail }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; margin: 0; padding: 24px; color: #111; }
        .sheet { max-width: {{ $format === 'thermal' ? '58mm' : '480px' }}; margin: 0 auto; }
        h1 { font-size: {{ $format === 'thermal' ? '13px' : '18px' }}; text-align: center; margin: 0 0 4px; }
        .center { text-align: center; }
        .sub { font-size: 11px; color: #555; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; font-size: {{ $format === 'thermal' ? '10px' : '12px' }}; }
        th, td { padding: 4px 2px; text-align: left; vertical-align: top; }
        th { border-bottom: 1px dashed #000; }
        td.right, th.right { text-align: right; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .status-badge {
            display: inline-block; padding: 3px 10px; border: 1px solid #000; border-radius: 4px;
            font-weight: bold; font-size: {{ $format === 'thermal' ? '11px' : '13px' }}; margin-top: 4px;
        }
        .catatan { border: 1px dashed #999; padding: 8px; margin-top: 8px; font-size: {{ $format === 'thermal' ? '10px' : '11px' }}; }
        .signature { display: flex; justify-content: space-between; margin-top: 32px; }
        .signature div { text-align: center; width: 45%; }
        .signature .line { border-top: 1px solid #000; margin-top: 40px; padding-top: 4px; }
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
        <p class="center sub">Surat Perintah Kerja (SPK)</p>

        <table>
            <tr><td>No. SPK</td><td class="right">#{{ $detail->id_detail }}</td></tr>
            <tr><td>No. Nota</td><td class="right">{{ $detail->nomor_nota }}</td></tr>
            <tr><td>Tanggal Order</td><td class="right">{{ \Carbon\Carbon::parse($detail->tgl_order)->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Konsumen</td><td class="right">{{ $detail->nama_konsumen }}</td></tr>
            <tr><td>Divisi/Mesin</td><td class="right">{{ $detail->divisi ?? '-' }}</td></tr>
        </table>

        <div class="divider"></div>

        <table>
            <tr><td>Nama File</td><td class="right">{{ $detail->nama_file }}</td></tr>
            <tr><td>Produk</td><td class="right">{{ $detail->nama_produk }}</td></tr>
            @if($detail->metode_hitung === 'ukuran')
            <tr><td>Ukuran</td><td class="right">{{ $detail->p_ukuran }} x {{ $detail->l_ukuran }} cm</td></tr>
            @endif
            <tr><td>Qty Order</td><td class="right">{{ $detail->qty }}</td></tr>
            <tr><td>Qty Cetak</td><td class="right">{{ $detail->qty_cetak ?? '-' }}</td></tr>
            @if($detail->status_urgent)
            <tr><td colspan="2" class="right" style="font-weight:bold;">*** URGENT ***</td></tr>
            @endif
        </table>

        <div class="divider"></div>

        <p class="center">
            Status: <span class="status-badge">{{ strtoupper($detail->status_spk) }}</span>
        </p>

        @if($detail->catatan_produksi)
        <div class="catatan">
            <strong>Catatan Produksi:</strong><br>
            {{ $detail->catatan_produksi }}
        </div>
        @endif

        @if($format !== 'thermal')
        <div class="signature">
            <div>
                <div class="line">Diambil Oleh</div>
            </div>
            <div>
                <div class="line">Diperiksa Oleh</div>
            </div>
        </div>
        @endif
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>
</body>
</html>

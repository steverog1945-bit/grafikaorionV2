<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penawaran {{ $quotation->nomor }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #1e293b; margin: 32px; font-size: 13px; }
        h1 { font-size: 20px; margin: 0 0 4px; } .muted { color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
        .r { text-align: right; } .total td { font-weight: bold; border-bottom: 0; }
        .kop { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1e293b; padding-bottom: 12px; }
        .aksi { margin-bottom: 16px; } @media print { .aksi { display: none; } body { margin: 12mm; } }
    </style>
</head>
<body>
    @php $app = \App\Models\AppSetting::current(); @endphp
    <div class="aksi"><button onclick="window.print()">Cetak / Simpan PDF</button></div>

    <div class="kop">
        <div>
            <h1>{{ $app->nama_aplikasi }}</h1>
            <div class="muted">{{ $app->alamat }}<br>{{ $app->telepon }}{{ $app->email ? ' | ' . $app->email : '' }}</div>
        </div>
        <div style="text-align:right">
            <h1>PENAWARAN</h1>
            <div><b>{{ $quotation->nomor }}</b></div>
            <div class="muted">Tanggal {{ $quotation->tanggal->format('d M Y') }}<br>Berlaku sampai {{ $quotation->berlaku_sampai->format('d M Y') }}</div>
        </div>
    </div>

    <p style="margin-top:16px">Kepada Yth.<br><b>{{ $quotation->nama_pelanggan }}</b>{{ $quotation->telepon ? ' (' . $quotation->telepon . ')' : '' }}</p>

    <table>
        <thead>
            <tr><th>#</th><th>Item</th><th class="r">Qty</th><th class="r">Harga</th><th class="r">Finishing</th><th class="r">Desain</th><th class="r">Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $i => $it)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><b>{{ $it->nama_item }}</b>@if($it->tipe === 'Produk' && $it->p_ukuran > 0) <span class="muted">{{ rtrim(rtrim($it->p_ukuran, '0'), '.') }} x {{ rtrim(rtrim($it->l_ukuran, '0'), '.') }} cm</span>@endif
                    @if($it->catatan)<div class="muted">{{ $it->catatan }}</div>@endif</td>
                <td class="r">{{ angka($it->qty, 2) }}</td>
                <td class="r">{{ number_format($it->harga_satuan, 0, ',', '.') }}</td>
                <td class="r">{{ $it->biaya_finishing > 0 ? number_format($it->biaya_finishing, 0, ',', '.') : '-' }}</td>
                <td class="r">{{ $it->biaya_desain > 0 ? number_format($it->biaya_desain, 0, ',', '.') : '-' }}</td>
                <td class="r">{{ number_format($it->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="6" class="r">Subtotal</td><td class="r">{{ number_format($quotation->subtotal, 0, ',', '.') }}</td></tr>
            @if($quotation->diskon > 0)<tr><td colspan="6" class="r">Diskon</td><td class="r">- {{ number_format($quotation->diskon, 0, ',', '.') }}</td></tr>@endif
            <tr class="total"><td colspan="6" class="r">TOTAL</td><td class="r">Rp {{ number_format($quotation->total, 0, ',', '.') }}</td></tr>
        </tfoot>
    </table>

    @if($quotation->catatan)<p style="margin-top:20px"><b>Catatan:</b><br>{!! nl2br(e($quotation->catatan)) !!}</p>@endif
    <p class="muted" style="margin-top:24px">Harga di atas berlaku sampai {{ $quotation->berlaku_sampai->format('d M Y') }}. Terima kasih atas kepercayaan Anda.</p>
</body>
</html>

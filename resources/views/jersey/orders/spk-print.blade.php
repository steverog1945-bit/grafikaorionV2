<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK Jersey {{ $order->order_number }} - {{ $line->nama_barang }}</title>
    @php
        $appSettingSpk = \App\Models\AppSetting::current();
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, 'Segoe UI', 'Inter', Roboto, sans-serif;
            margin: 0; padding: 24px; color: #1e293b; background: #e2e8f0;
        }
        .sheet {
            max-width: 780px; margin: 0 auto; background: #fff;
            border: 1.5px solid #334155; border-radius: 4px; overflow: hidden;
        }
        .bar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 20px; border-bottom: 1.5px solid #334155;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
            color: #475569; background: #f8fafc;
        }
        .title-row { display: flex; border-bottom: 1.5px solid #334155; }
        .title-row .left { flex: 1; padding: 18px 20px; border-right: 1.5px solid #334155; }
        .title-row .right { flex: 1.3; padding: 18px 20px; display: flex; align-items: baseline; justify-content: space-between; }
        .title-row h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: 0.02em; color: #0f172a; }
        .title-row .right h1 { font-size: 32px; }
        .title-row .code { font-size: 12px; font-weight: 700; color: #64748b; }

        table.info { width: 100%; border-collapse: collapse; }
        table.info td { padding: 10px 20px; font-size: 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        table.info td.label { width: 32%; font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; border-right: 1.5px solid #334155; }
        table.info td.value { font-weight: 700; color: #0f172a; }

        .keterangan-box { padding: 14px 20px; border-bottom: 1.5px solid #334155; background: #f8fafc; }
        .keterangan-box p { margin: 0 0 4px; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; }
        .keterangan-box pre { margin: 0; font-family: inherit; font-size: 11.5px; white-space: pre-wrap; color: #334155; line-height: 1.6; }

        .body-row { display: flex; border-bottom: 1.5px solid #334155; }
        .thumb-col { flex: 1.4; padding: 16px; border-right: 1.5px solid #334155; display: flex; align-items: center; justify-content: center; background: #f8fafc; min-height: 220px; }
        .thumb-col img { max-width: 100%; max-height: 260px; object-fit: contain; }
        .thumb-col .placeholder { color: #cbd5e1; font-size: 11px; text-align: center; }

        .ukuran-col { flex: 1; display: flex; flex-direction: column; }
        .ukuran-col .head { padding: 8px 16px; text-align: center; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #475569; border-bottom: 1.5px solid #334155; background: #f1f5f9; }
        table.ukuran { width: 100%; border-collapse: collapse; flex: 1; }
        table.ukuran th, table.ukuran td { border: 1px solid #cbd5e1; padding: 6px 4px; text-align: center; font-size: 11px; }
        table.ukuran th { background: #f8fafc; font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 9px; }
        table.ukuran td.qty { font-weight: 800; color: #0f172a; }
        .total-pcs { padding: 10px 16px; text-align: center; background: #0f172a; color: #fff; font-weight: 800; font-size: 13px; letter-spacing: 0.04em; }

        .spek-title { padding: 10px 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #fff; background: #334155; }
        table.spek { width: 100%; border-collapse: collapse; }
        table.spek th, table.spek td { border: 1px solid #e2e8f0; padding: 8px 10px; font-size: 11px; text-align: left; }
        table.spek th { background: #f8fafc; font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 9px; }
        table.spek td.empty-row { height: 26px; }

        .note-strip { padding: 10px 20px; font-size: 10px; color: #64748b; border-top: 1px solid #e2e8f0; }
        .signature { display: flex; }
        .signature > div { flex: 1; padding: 24px 20px 20px; text-align: center; }
        .signature > div:first-child { border-right: 1.5px solid #334155; }
        .signature p.role { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin: 0 0 44px; }
        .signature .line { border-top: 1px solid #334155; width: 70%; margin: 0 auto; }

        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { padding: 10px 28px; font-family: inherit; font-weight: 700; cursor: pointer; background: #334155; color: #fff; border: none; border-radius: 10px; font-size: 13px; }
        .no-print button:hover { background: #1e293b; }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { border: none; border-radius: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="bar">
            <span>Periode {{ \Carbon\Carbon::parse($order->order_date)->translatedFormat('F Y') }}</span>
            <span>{{ $appSettingSpk->nama_aplikasi }}</span>
        </div>

        <div class="title-row">
            <div class="left">
                <h1>Pre-Order</h1>
            </div>
            <div class="right">
                <h1>SPK</h1>
                <span class="code">Code: {{ $order->order_number }}-{{ $line->id }}</span>
            </div>
        </div>

        <table class="info">
            <tr>
                <td class="label">Nama Barang</td>
                <td class="value">{{ $line->nama_barang }}</td>
            </tr>
            <tr>
                <td class="label">Nama Pemesan</td>
                <td class="value">{{ $order->konsumen->nama_konsumen ?? $order->customer_name }}{{ $order->team_name ? ' (' . $order->team_name . ')' : '' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Masuk</td>
                <td class="value">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Beres</td>
                <td class="value">{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Bahan</td>
                <td class="value">{{ $line->bahan ?? '-' }}</td>
            </tr>
        </table>

        @if($line->keterangan)
        <div class="keterangan-box">
            <p>Keterangan</p>
            <pre>{{ $line->keterangan }}</pre>
        </div>
        @endif

        <div class="body-row">
            <div class="thumb-col">
                @if($line->thumbnail)
                    <img src="{{ asset('storage/' . $line->thumbnail) }}" alt="Thumbnail Desain">
                @else
                    <span class="placeholder">Belum ada thumbnail desain</span>
                @endif
            </div>
            <div class="ukuran-col">
                <div class="head">Ukuran</div>
                @php
                    $sizeMap = $line->sizeQuantities->mapWithKeys(fn($sq) => [$sq->size->name ?? '-' => $sq->quantity]);
                    if ($line->items->isNotEmpty()) {
                        $sizeMap = $line->items->groupBy('size')->map->count();
                    }
                    $ukuranUtama = ['S', 'M', 'L', 'XL', '2XL', '3XL'];
                    $ukuranBawah = ['4XL', '5XL'];
                @endphp
                <table class="ukuran">
                    <tr>@foreach($ukuranUtama as $u)<th>{{ $u }}</th>@endforeach</tr>
                    <tr>@foreach($ukuranUtama as $u)<td class="qty">{{ $sizeMap[$u] ?? '' }}</td>@endforeach</tr>
                    <tr>@foreach($ukuranBawah as $u)<th>{{ $u }}</th>@endforeach<th></th><th></th><th></th><th></th></tr>
                    <tr>@foreach($ukuranBawah as $u)<td class="qty">{{ $sizeMap[$u] ?? '' }}</td>@endforeach<td></td><td></td><td></td><td></td></tr>
                </table>
                <div class="total-pcs">TOTAL {{ $line->qty }} PCS</div>
            </div>
        </div>

        <div class="spek-title">Spesifikasi Order</div>
        <table class="spek">
            <thead>
                <tr><th>No</th><th>Nama Barang</th><th>Warna</th><th colspan="3">Penggunaan Bahan</th><th>Keterangan</th></tr>
                <tr><th></th><th></th><th></th><th>Kg</th><th>Meter</th><th>Yard</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($line->materials as $i => $m)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $m->nama_barang }}</td>
                    <td>{{ $m->warna ?? '-' }}</td>
                    <td>{{ $m->penggunaan_kg ?? '-' }}</td>
                    <td>{{ $m->penggunaan_meter ?? '-' }}</td>
                    <td>{{ $m->penggunaan_yard ?? '-' }}</td>
                    <td>{{ $m->keterangan ?? '-' }}</td>
                </tr>
                @empty
                    @for($i = 0; $i < 4; $i++)
                    <tr><td class="empty-row">{{ $i + 1 }}</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    @endfor
                @endforelse
            </tbody>
        </table>

        <div class="note-strip">Wajib tanda tangan setiap proses dan SPK wajib dikembalikan bersama barangnya.</div>

        <div class="signature">
            <div><p class="role">Penerima</p><div class="line"></div></div>
            <div><p class="role">{{ $appSettingSpk->nama_aplikasi }}</p><div class="line"></div></div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak SPK</button>
    </div>
</body>
</html>

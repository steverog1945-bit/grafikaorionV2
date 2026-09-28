<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Order Jersey {{ $order->order_number }}</title>
    @include('partials.print-style')
    @if($format !== 'thermal')
    <style>.sheet { max-width: 680px; }</style>
    @endif
    {{--
        Rapatkan margin/spasi di SEMUA bagian ringkasan KECUALI tabel
        rincian barang - permintaan langsung user, tabel rincian barang
        sengaja dibiarkan pakai padding baris default (lebih lega) supaya
        tetap mudah dibaca per barang.
    --}}
    <style>
        .ringkasan-jersey .print-header { margin-bottom: 8px; }
        .ringkasan-jersey .signature { margin-top: 20px; }

        {{-- Info order rapat: label & nilai duduk BERDAMPINGAN dalam satu
             aliran baca kiri ("Pelanggan: Budi"), bukan label nempel kiri
             + nilai terdorong ke tepi kanan yang memaksa mata bolak-balik -
             permintaan langsung user supaya mudah dibaca. --}}
        .info-rapat td { padding: 1.5px 4px; vertical-align: top; }
        .info-rapat .lbl { white-space: nowrap; color: #475569; font-weight: 600; padding-right: 6px; }
    </style>
</head>
<body>
    <div class="sheet ringkasan-jersey">
        @include('partials.print-header', ['docTitle' => 'Ringkasan Order Jersey'])

        {{-- Info order rapat kiri: pelanggan paling atas (paling sering
             dicari), label & nilainya bersebelahan supaya terbaca sekali
             jalan. Tetap satu kolom sempit, muat di A4. --}}
        <table class="table-compact info-rapat">
            <tr>
                <td class="lbl">Pelanggan:</td>
                <td><strong>{{ $order->konsumen->nama_konsumen ?? $order->customer_name }}</strong>@if($order->konsumen?->telepon) &middot; {{ $order->konsumen->telepon }}@endif</td>
            </tr>
            <tr><td class="lbl">No. Order:</td><td>{{ $order->order_number }}</td></tr>
            <tr><td class="lbl">Tanggal:</td><td>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td></tr>
            <tr><td class="lbl">Tim:</td><td>{{ $order->team_name ?? '-' }}</td></tr>
            <tr><td class="lbl">Desainer:</td><td>{{ $order->designer_name ?? '-' }}</td></tr>
            <tr><td class="lbl">Deadline:</td><td>{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('d/m/Y') : '-' }}</td></tr>
        </table>

        <div class="divider divider-tight"></div>

        <table>
            <thead><tr><th>Barang</th><th>Tipe/Kategori</th><th class="right">Qty</th><th class="right">Subtotal</th></tr></thead>
            <tbody>
                @foreach($order->lines as $line)
                <tr>
                    <td>{{ $line->nama_barang }}</td>
                    <td>{{ $line->productType->name ?? '-' }} / {{ $line->category->name ?? '-' }}</td>
                    <td class="right">{{ angka($line->qty, 4) }}</td>
                    <td class="right">{{ number_format($line->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($order->lines->contains(fn ($l) => $l->materials->isNotEmpty()))
        <div class="divider divider-tight"></div>
        <table>
            <thead><tr><th>Kebutuhan Bahan (estimasi)</th><th class="right">Meter</th><th class="right">Berat</th></tr></thead>
            <tbody>
                @foreach($order->lines as $line)
                    @if($line->materials->isNotEmpty())
                    <tr><td colspan="3"><strong>{{ $line->nama_barang }}</strong> ({{ angka($line->qty, 4) }} pcs)</td></tr>
                    @foreach($line->materials as $m)
                    <tr>
                        <td>&nbsp;&nbsp;{{ $m->nama_barang }}</td>
                        <td class="right">{{ $m->penggunaan_meter !== null ? number_format($m->penggunaan_meter, 2, ',', '.') . ' m' : '-' }}</td>
                        <td class="right">{{ $m->penggunaan_kg !== null ? \App\Services\JerseyMaterialEstimationService::formatBeratGram($m->penggunaan_kg * 1000) : '-' }}</td>
                    </tr>
                    @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
        @endif

        @php
            $totalBeratKg = $order->lines->flatMap->materials->sum('penggunaan_kg');

            $totalUkuranOrder = [];
            foreach ($order->lines as $line) {
                foreach ($line->sizeQuantities as $sq) {
                    $nama = $sq->size->name ?? '-';
                    $sort = $sq->size->sort_order ?? 0;
                    if (!isset($totalUkuranOrder[$nama])) {
                        $totalUkuranOrder[$nama] = ['qty' => 0, 'sort' => $sort];
                    }
                    $totalUkuranOrder[$nama]['qty'] += $sq->quantity;
                }
            }
            uasort($totalUkuranOrder, fn ($a, $b) => $a['sort'] <=> $b['sort']);
        @endphp
        @if(!empty($totalUkuranOrder) || $totalBeratKg > 0)
        <div class="divider divider-tight"></div>
        <table class="table-compact">
            @if(!empty($totalUkuranOrder))
            <tr><td>Total Ukuran</td><td class="right">{{ collect($totalUkuranOrder)->map(fn ($v, $k) => "{$k}: {$v['qty']}")->implode(', ') }}</td></tr>
            @endif
            @if($totalBeratKg > 0)
            <tr><td>Estimasi Total Berat Bahan</td><td class="right">{{ \App\Services\JerseyMaterialEstimationService::formatBeratGram($totalBeratKg * 1000) }}</td></tr>
            @endif
        </table>
        @endif

        <div class="divider divider-tight"></div>

        <table class="table-compact">
            <tr><td>Diskon</td><td class="right">Rp {{ number_format($order->discount, 0, ',', '.') }}</td></tr>
            <tr><td>Biaya Tambahan</td><td class="right">Rp {{ number_format($order->additional_charge, 0, ',', '.') }}</td></tr>
            <tr class="total-row"><td>TOTAL</td><td class="right">Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
            <tr><td>Deposit/DP</td><td class="right">Rp {{ number_format($order->deposit, 0, ',', '.') }}</td></tr>
        </table>

        @if($order->notes)
        <div class="divider divider-tight"></div>
        <div class="catatan">{{ $order->notes }}</div>
        @endif

        <div class="signature">
            <div><div class="line">Pemesan</div></div>
            <div><div class="line">Admin</div></div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>
</body>
</html>

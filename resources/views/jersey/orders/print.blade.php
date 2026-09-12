<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Order Jersey {{ $order->order_number }}</title>
    @include('partials.print-style')
    @if($format !== 'thermal')
    <style>.sheet { max-width: 680px; }</style>
    @endif
</head>
<body>
    <div class="sheet">
        @include('partials.print-header', ['docTitle' => 'Ringkasan Order Jersey'])

        <table>
            <tr><td>No. Order</td><td class="right">{{ $order->order_number }}</td></tr>
            <tr><td>Tanggal</td><td class="right">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td></tr>
            <tr><td>Pelanggan</td><td class="right">{{ $order->konsumen->nama_konsumen ?? $order->customer_name }}</td></tr>
            <tr><td>Tim</td><td class="right">{{ $order->team_name ?? '-' }}</td></tr>
            <tr><td>Deadline</td><td class="right">{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('d/m/Y') : '-' }}</td></tr>
            <tr><td>Desainer</td><td class="right">{{ $order->designer_name ?? '-' }}</td></tr>
        </table>

        <div class="divider"></div>

        <table>
            <thead><tr><th>Barang</th><th>Tipe/Kategori</th><th class="right">Qty</th><th class="right">Subtotal</th></tr></thead>
            <tbody>
                @foreach($order->lines as $line)
                <tr>
                    <td>{{ $line->nama_barang }}</td>
                    <td>{{ $line->productType->name ?? '-' }} / {{ $line->category->name ?? '-' }}</td>
                    <td class="right">{{ $line->qty }}</td>
                    <td class="right">{{ number_format($line->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

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
        <div class="divider"></div>
        <table>
            @if(!empty($totalUkuranOrder))
            <tr><td>Total Ukuran</td><td class="right">{{ collect($totalUkuranOrder)->map(fn ($v, $k) => "{$k}: {$v['qty']}")->implode(', ') }}</td></tr>
            @endif
            @if($totalBeratKg > 0)
            <tr><td>Estimasi Total Berat Bahan</td><td class="right">{{ \App\Services\JerseyMaterialEstimationService::formatBeratGram($totalBeratKg * 1000) }}</td></tr>
            @endif
        </table>
        @endif

        <div class="divider"></div>

        <table>
            <tr><td>Diskon</td><td class="right">Rp {{ number_format($order->discount, 0, ',', '.') }}</td></tr>
            <tr><td>Biaya Tambahan</td><td class="right">Rp {{ number_format($order->additional_charge, 0, ',', '.') }}</td></tr>
            <tr class="total-row"><td>TOTAL</td><td class="right">Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
            <tr><td>Deposit/DP</td><td class="right">Rp {{ number_format($order->deposit, 0, ',', '.') }}</td></tr>
        </table>

        @if($order->notes)
        <div class="divider"></div>
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

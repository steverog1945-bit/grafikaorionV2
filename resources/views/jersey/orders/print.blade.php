<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Order Jersey {{ $order->order_number }}</title>
    @php($format = 'normal')
    @include('partials.print-style')
</head>
<body>
    <div class="sheet" style="max-width: 620px;">
        @include('partials.print-header', ['docTitle' => 'Order Jersey'])

        <table>
            <tr><td>No. Order</td><td class="right">{{ $order->order_number }}</td></tr>
            <tr><td>Tanggal</td><td class="right">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td></tr>
            <tr><td>Pelanggan</td><td class="right">{{ $order->customer_name }}</td></tr>
            <tr><td>Tim</td><td class="right">{{ $order->team_name ?? '-' }}</td></tr>
            <tr><td>Tipe / Kategori</td><td class="right">{{ $order->productType->name ?? '-' }} / {{ $order->category->name ?? '-' }}</td></tr>
            <tr><td>Deadline</td><td class="right">{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('d/m/Y') : '-' }}</td></tr>
            <tr><td>Desainer</td><td class="right">{{ $order->designer_name ?? '-' }}</td></tr>
        </table>

        <div class="divider"></div>

        @if($order->items->isNotEmpty())
        <table>
            <thead>
                <tr><th>Nama Pemain</th><th>No.</th><th>Ukuran</th><th>Gender</th><th>Lengan</th><th>Kerah</th></tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->player_name }}</td>
                    <td>{{ $item->player_number ?? '-' }}</td>
                    <td>{{ $item->size }}</td>
                    <td>{{ $item->gender }}</td>
                    <td>{{ $item->sleeve }}</td>
                    <td>{{ $item->collar ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($order->sizeQuantities->isNotEmpty())
        <table>
            <thead><tr><th>Ukuran</th><th class="right">Qty</th></tr></thead>
            <tbody>
                @foreach($order->sizeQuantities as $sq)
                <tr><td>{{ $sq->size->name ?? '-' }}</td><td class="right">{{ $sq->quantity }}</td></tr>
                @endforeach
            </tbody>
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

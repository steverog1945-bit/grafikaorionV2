<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK Jersey {{ $order->order_number }} - {{ $line->nama_barang }}</title>
    @include('partials.spk-print-style')
</head>
<body>
    @include('jersey.orders._spk-sheet', ['order' => $order, 'line' => $line])

    <div class="no-print">
        <button onclick="window.print()">Cetak SPK</button>
    </div>
</body>
</html>

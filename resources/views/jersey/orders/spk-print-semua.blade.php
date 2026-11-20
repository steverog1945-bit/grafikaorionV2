<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK Jersey {{ $order->order_number }} - Semua Barang</title>
    @include('partials.spk-print-style')
    <style>
        /* Satu barang = satu halaman cetak. Di layar dokumennya ditumpuk
           dengan jarak supaya operator tetap bisa memeriksa semuanya. */
        .spk-copy + .spk-copy { margin-top: 18px; }
        @media print {
            .spk-copy { break-after: page; page-break-after: always; margin: 0; }
            .spk-copy:last-child { break-after: auto; page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width:760px;margin:12px auto 0;padding:0 16px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
        <span style="font-size:12px;">{{ $lines->count() }} barang dalam order ini &middot; tiap barang dicetak di halaman terpisah.</span>
        <button onclick="window.print()">Cetak Semua SPK</button>
    </div>

    @foreach($lines as $line)
        <div class="spk-copy">
            @include('jersey.orders._spk-sheet', ['order' => $order, 'line' => $line])
        </div>
    @endforeach
</body>
</html>

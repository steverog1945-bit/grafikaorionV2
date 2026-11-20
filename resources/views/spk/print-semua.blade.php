<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK Nota {{ $details->first()->nomor_nota }} - Semua Item</title>
    @include('partials.print-style')
    <style>
        /* Satu item SPK = satu halaman cetak. Di layar dokumennya ditumpuk
           dengan jarak supaya operator bisa memeriksa semuanya dulu. */
        .spk-copy + .spk-copy { margin-top: 14px; }
        @media print {
            .spk-copy { break-after: page; page-break-after: always; margin: 0; }
            .spk-copy:last-child { break-after: auto; page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 4px;">
        <span style="font-size:12px;">{{ $details->count() }} item SPK dari nota {{ $details->first()->nomor_nota }} &middot; tiap item di halaman terpisah.</span>
        <button onclick="window.print()">🖨️ Cetak Semua</button>
    </div>

    @foreach($details as $detail)
        <div class="spk-copy">
            @include('spk._spk-sheet', ['finishingItems' => $finishingItems[$detail->id_detail] ?? collect()])
        </div>
    @endforeach

    @include('spk._qr-script', ['details' => $details, 'format' => $format])
</body>
</html>

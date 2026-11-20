<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK #{{ $detail->id_detail }}</title>
    @include('partials.print-style')
</head>
<body>
    @include('spk._spk-sheet')

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>

    @include('spk._qr-script', ['details' => collect([$detail]), 'format' => $format])
</body>
</html>

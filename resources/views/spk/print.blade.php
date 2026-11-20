<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK #{{ $detail->id_detail }}</title>
    @include('partials.print-style')
    @if($format !== 'thermal')
    <style>
        /* Portrait A4: kurangi jeda vertikal dan padding sel supaya
           satu halaman SPK lengkap masih muat di satu lembaran. */
        .print-header { margin-bottom: 8px; }
        table td { padding: 2px 4px; }
        .divider { margin: 4px 0; }
        .catatan { margin-top: 8px; }
    </style>
    @endif
</head>
<body>
    @include('spk._spk-sheet')

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>

    @include('spk._qr-script', ['details' => collect([$detail]), 'format' => $format])
</body>
</html>

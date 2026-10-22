<?php
$files = [
    'resources/views/layouts/app.blade.php',
    'resources/views/penjualan/create.blade.php',
    'resources/views/penjualan/show.blade.php',
    'resources/views/penjualan/riwayat.blade.php',
    'resources/views/pengaturan/aplikasi.blade.php',
    'resources/views/penjualan/create.blade.php',
];
$chars = [0x008F, 0x00A2, 0x00A8, 0x00B8, 0x00E2, 0x00EF, 0x00F0, 0x0178];
foreach ($files as $f) {
    $content = file_get_contents($f);
    $len = mb_strlen($content, 'UTF-8');
    $found = [];
    for ($i = 0; $i < $len; $i++) {
        $ch = mb_substr($content, $i, 1, 'UTF-8');
        $cp = mb_ord($ch, 'UTF-8');
        if (in_array($cp, $chars)) {
            $ctx = mb_substr($content, max(0, $i-30), 60, 'UTF-8');
            $found[] = "U+" . sprintf('%04X', $cp) . " at pos $i: ..." . $ctx . "...";
            if (count($found) > 3) break;
        }
    }
    if ($found) {
        echo "=== $f ===\n";
        foreach ($found as $f2) echo "  $f2\n";
    }
}
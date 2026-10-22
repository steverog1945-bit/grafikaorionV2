<?php
$files = [
    'app/Http/Controllers/PenjualanController.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/penjualan/create.blade.php',
    'resources/views/penjualan/show.blade.php',
    'resources/views/penjualan/riwayat.blade.php',
    'routes/web.php',
];
foreach ($files as $f) {
    $content = file_get_contents($f);
    $lines = preg_split('/\R/', $content);
    $nonAsciiLines = [];
    foreach ($lines as $no => $line) {
        if (preg_match('/[^\x00-\x7F]/u', $line)) {
            $nonAsciiLines[] = "  L" . ($no+1) . ": " . mb_substr(trim($line), 0, 100);
        }
    }
    if ($nonAsciiLines) {
        echo "=== $f ===\n";
        foreach ($nonAsciiLines as $l) echo "$l\n";
        echo "\n";
    }
}
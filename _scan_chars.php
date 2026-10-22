<?php
$files = [
    'app/Http/Controllers/PenjualanController.php',
    'app/Http/Controllers/PostinganController.php',
    'app/Http/Controllers/SettingAplikasiController.php',
    'app/Models/AppSetting.php',
    'app/Console/Commands/AuditIntegritasCommand.php',
    'routes/web.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/penjualan/create.blade.php',
    'resources/views/penjualan/show.blade.php',
    'resources/views/penjualan/riwayat.blade.php',
    'resources/views/pengaturan/aplikasi.blade.php',
    'tests/Feature/PenjualanSusulanModeTest.php',
];
$chars = [];
foreach ($files as $f) {
    $content = file_get_contents($f);
    preg_match_all('/[^\x00-\x7F]/u', $content, $m);
    foreach ($m[0] as $ch) {
        if (!isset($chars[$ch])) $chars[$ch] = ['n' => 0, 'files' => []];
        $chars[$ch]['n']++;
        if (!in_array($f, $chars[$ch]['files'])) $chars[$ch]['files'][] = $f;
    }
}
ksort($chars);
echo "Total non-ASCII unik: " . count($chars) . "\n\n";
foreach ($chars as $ch => $info) {
    $cp = mb_ord($ch, 'UTF-8');
    echo sprintf("U+%04X | %-4s | %4d | %s\n", $cp, $ch, $info['n'], implode(', ', array_map('basename', $info['files'])));
}
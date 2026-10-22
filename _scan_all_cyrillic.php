<?php
/**
 * Skrip UJI (sementara) - scan SELURUH project (kecuali vendor/node_modules)
 * untuk karakter Cyrillic. DIHAPUS setelah dipakai.
 */
$root = __DIR__;
$skip = ['vendor', 'node_modules', '.git', 'storage/framework/views', 'storage/framework/cache', 'storage/logs', 'storage/framework/sessions'];
$cyrl = '/[\x{0400}-\x{04ff}\x{0500}-\x{052f}\x{2de0}-\x{2dff}\x{a640}-\x{a69f}\x{1c80}-\x{1c8f}]/u';
$hits = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $path = str_replace($root . DIRECTORY_SEPARATOR, '', $f->getPathname());
    foreach ($skip as $s) { if (strpos($path, $s . DIRECTORY_SEPARATOR) === 0 || strpos($path, $s) === 0) continue 2; }
    $ext = strtolower($f->getExtension());
    if (!in_array($ext, ['php','blade','js','css','txt','md','json','html','sql'], true)) continue;
    $content = @file_get_contents($f->getPathname());
    if ($content === false) continue;
    if (!preg_match($cyrl, $content)) continue;
    foreach (preg_split('/\R/', $content) as $no => $baris) {
        if (preg_match($cyrl, $baris)) {
            $potong = mb_substr(trim($baris), 0, 90);
            $hits[] = $path . ' baris ' . ($no+1) . ': ' . $potong;
            if (count($hits) >= 40) break 2;
        }
    }
}
echo "ditemukan: " . count($hits) . " baris\n";
foreach ($hits as $h) echo "  $h\n";
if (count($hits) === 0) echo "  (bersih - tidak ada Cyrillic di seluruh project)\n";
<?php
$root = __DIR__;
$dirs = ['app', 'resources'];
$exts = ['php','js','css','blade'];
$chars = [];
foreach ($dirs as $d) {
    $base = $root . DIRECTORY_SEPARATOR . $d;
    if (!is_dir($base)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($it as $f) {
        if (!$f->isFile()) continue;
        if (!in_array(strtolower($f->getExtension()), $exts, true)) continue;
        $content = @file_get_contents($f->getPathname());
        if ($content === false) continue;
        $len = strlen($content);
        for ($i = 0; $i < $len; $i++) {
            $b = ord($content[$i]);
            if ($b < 128) continue;
            if ($b >= 0xC0 && $b < 0xE0) { $cp = (($b & 0x1F) << 6) | (ord($content[$i+1]) & 0x3F); $i += 1; }
            elseif ($b >= 0xE0 && $b < 0xF0) { $cp = (($b & 0x0F) << 12) | ((ord($content[$i+1]) & 0x3F) << 6) | (ord($content[$i+2]) & 0x3F); $i += 2; }
            elseif ($b >= 0xF0) { $cp = (($b & 0x07) << 18) | ((ord($content[$i+1]) & 0x3F) << 12) | ((ord($content[$i+2]) & 0x3F) << 6) | (ord($content[$i+3]) & 0x3F); $i += 3; }
            else continue;
            if ($cp < 128) continue;
            if (!isset($chars[$cp])) $chars[$cp] = 0;
            $chars[$cp]++;
        }
    }
}
ksort($chars);
echo "Total non-ASCII unik: " . count($chars) . "\n";
foreach ($chars as $cp => $cnt) {
    $ch = mb_chr($cp, 'UTF-8');
    echo sprintf("U+%04X | %s | %d\n", $cp, $ch, $cnt);
}
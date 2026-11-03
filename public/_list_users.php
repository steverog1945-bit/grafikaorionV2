<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (($_GET['t'] ?? '') !== 'xK9mP2qR7vL4') { http_response_code(403); exit('forbidden'); }

$users = \Illuminate\Support\Facades\DB::table('users')->select('id_user', 'username', 'id_karyawan')->limit(5)->get();
echo json_encode($users);

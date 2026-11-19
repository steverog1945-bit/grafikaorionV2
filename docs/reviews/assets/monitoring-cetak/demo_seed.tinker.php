<?php

use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User;
use App\Models\MonitoringAgent;
use App\Models\MonitoringMachine;
use App\Models\MonitoringEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Data demo SINTETIS untuk Monitoring Cetak di DB terisolasi
// (percetakan_erp_testing @ 127.0.0.1:3307). Bukan data produksi.

DB::table('monitoring_events')->delete();
DB::table('monitoring_agents')->delete();
DB::table('monitoring_mesins')->delete();

$role = Role::firstOrCreate(['nama_role' => 'Produksi'], ['keterangan' => 'Monitoring Cetak demo']);
$user = User::where('username', 'demoproduksi')->first();
if (! $user) {
    $kar = Karyawan::factory()->create(['nama_karyawan' => 'Demo Produksi']);
    $user = new User();
    $user->forceFill([
        'username' => 'demoproduksi',
        'password' => Hash::make('demo12345'),
        'id_karyawan' => $kar->id_karyawan,
        'id_role' => $role->id_role,
    ])->save();
} else {
    $user->update(['id_role' => $role->id_role]);
}

DB::table('menus')->insertOrIgnore(['id_menu' => 22, 'nama_menu' => 'SPK', 'url_link' => 'spk', 'parent_id' => 0]);
DB::table('role_permissions')->where('id_role', $role->id_role)->delete();
DB::table('role_permissions')->insert([
    ['id_role' => $role->id_role, 'id_menu' => 1],
    ['id_role' => $role->id_role, 'id_menu' => 22],
]);

$mesinSintetis = [
    ['nama_mesin' => 'EPSON S80670', 'tipe_mesin' => 'Digital'],
    ['nama_mesin' => 'RHINO DTF', 'tipe_mesin' => 'DTF'],
    ['nama_mesin' => 'KONICA MINOLTA', 'tipe_mesin' => 'Produksi'],
];
foreach ($mesinSintetis as $m) {
    if (DB::table('mesin')->where('nama_mesin', $m['nama_mesin'])->doesntExist()) {
        DB::table('mesin')->insert(['nama_mesin' => $m['nama_mesin'], 'tipe_mesin' => $m['tipe_mesin'], 'created_at' => now(), 'updated_at' => now()]);
    }
}
$mesinList = DB::table('mesin')->whereIn('nama_mesin', ['EPSON S80670', 'RHINO DTF', 'KONICA MINOLTA'])->get()->keyBy('nama_mesin');

$specs = [
    ['nama_mesin' => 'EPSON S80670', 'software' => 'onyx', 'pc_ip' => '192.168.1.31', 'printer_name' => 'EPSON SC-S80670'],
    ['nama_mesin' => 'RHINO DTF', 'software' => 'flexi', 'pc_ip' => '192.168.1.32', 'printer_name' => 'RHINO DTF PRO'],
    ['nama_mesin' => 'KONICA MINOLTA', 'software' => 'edgeprint', 'pc_ip' => '192.168.1.33', 'printer_name' => 'KONICA MINOLTA C368'],
];
$monitoring = [];
foreach ($specs as $s) {
    $monitoring[$s['nama_mesin']] = MonitoringMachine::create([
        'id_mesin' => $mesinList[$s['nama_mesin']]->id_mesin,
        'nama_mesin' => $s['nama_mesin'],
        'tipe_mesin' => $mesinList[$s['nama_mesin']]->tipe_mesin,
        'software' => $s['software'],
        'pc_ip' => $s['pc_ip'],
        'printer_name' => $s['printer_name'],
        'aktif' => true,
    ]);
}

$agent1 = MonitoringAgent::create([
    'monitoring_mesin_id' => $monitoring['EPSON S80670']->id,
    'nama' => 'PC-PRODUKSI-01',
    'token_hash' => hash('sha256', 'demo-token-s80670'),
    'aktif' => true,
    'last_seen_at' => now(),
    'last_ip' => '192.168.1.31',
]);
$agent2 = MonitoringAgent::create([
    'monitoring_mesin_id' => $monitoring['RHINO DTF']->id,
    'nama' => 'PC-PRODUKSI-02',
    'token_hash' => hash('sha256', 'demo-token-rhinodtf'),
    'aktif' => true,
    'last_seen_at' => now(),
    'last_ip' => '192.168.1.32',
]);

// Histori job selesai (ETA): mesin 1 ~15 menit, mesin 2 ~30-38 menit
$ended = fn ($monitoringId, string $doc, int $durasiMenit, $waktuSelesai) => [
    MonitoringEvent::create(['monitoring_mesin_id' => $monitoringId, 'event' => 'started', 'doc_name' => $doc, 'created_at' => $waktuSelesai->copy()->subMinutes($durasiMenit)]),
    MonitoringEvent::create(['monitoring_mesin_id' => $monitoringId, 'event' => 'completed', 'doc_name' => $doc, 'created_at' => $waktuSelesai]),
];
$now = now();
$ended($monitoring['EPSON S80670']->id, 'BANNER-RT-DESA.cdr', 14, $now->copy()->subDay()->subHours(3));
$ended($monitoring['EPSON S80670']->id, 'SPANDUK-TOKO-MINI.cdr', 16, $now->copy()->subDay()->subHours(1));
$ended($monitoring['EPSON S80670']->id, 'POSTER-KONSER.cdr', 15, $now->copy()->subHours(6));
$ended($monitoring['RHINO DTF']->id, 'JERSEY-ORANG-MUDA.tif', 32, $now->copy()->subDay()->subHours(4));
$ended($monitoring['RHINO DTF']->id, 'JERSEY-CLUB-BOLA.tif', 38, $now->copy()->subHours(7));

// Job berjalan SEKARANG di mesin 1: printing, 8 dari 20 halaman
MonitoringEvent::create(['monitoring_agent_id' => $agent1->id, 'monitoring_mesin_id' => $monitoring['EPSON S80670']->id, 'event' => 'started', 'printer' => 'EPSON SC-S80670', 'doc_name' => 'BANNER-GRAND-LAUNCHING.cdr', 'pages_total' => 20, 'pages_done' => 2, 'created_at' => now()->subMinutes(4)]);
MonitoringEvent::create(['monitoring_agent_id' => $agent1->id, 'monitoring_mesin_id' => $monitoring['EPSON S80670']->id, 'event' => 'progress', 'printer' => 'EPSON SC-S80670', 'doc_name' => 'BANNER-GRAND-LAUNCHING.cdr', 'pages_total' => 20, 'pages_done' => 8, 'created_at' => now()->subMinutes(1)]);
MonitoringEvent::create(['monitoring_agent_id' => $agent2->id, 'monitoring_mesin_id' => $monitoring['RHINO DTF']->id, 'event' => 'completed', 'printer' => 'RHINO DTF PRO', 'doc_name' => 'JERSEY-CLUB-BOLA.tif', 'created_at' => now()->subHours(1)]);

// Konsumen sintetis
$idGol = DB::table('golongan_konsumen')->where('nama_golongan', 'Retail')->value('id_golongan');
if (! $idGol) {
    $idGol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail']);
}
$konsumenTel = ['0811000111', '0811000222', '0811000333'];
foreach ([['Budi Santoso', '0811000111'], ['Siti Aminah', '0811000222'], ['Toko Berkah', '0811000333']] as $k) {
    if (DB::table('konsumen')->where('telepon', $k[1])->doesntExist()) {
        DB::table('konsumen')->insert(['id_unique' => (string) Str::uuid(), 'nama_konsumen' => $k[0], 'telepon' => $k[1], 'alamat' => '-', 'id_golongan' => $idGol]);
    }
}
$konsumenMap = DB::table('konsumen')->whereIn('telepon', $konsumenTel)->pluck('id_konsumen', 'telepon');

// Produk per mesin
foreach ([
    ['Spanduk Outdoor', $mesinList['EPSON S80670']->id_mesin, 'ukuran'],
    ['Jersey Sublim', $mesinList['RHINO DTF']->id_mesin, 'quantity'],
    ['Banner Indoor', $mesinList['KONICA MINOLTA']->id_mesin, 'ukuran'],
] as $p) {
    $exists = DB::table('produk')->where('nama_produk', $p[0])->first();
    if ($exists) {
        DB::table('produk')->where('id_produk', $exists->id_produk)->update(['id_mesin' => $p[1], 'metode_hitung' => $p[2]]);
    } else {
        DB::table('produk')->insert(['nama_produk' => $p[0], 'metode_hitung' => $p[2], 'status' => 'Aktif', 'id_mesin' => $p[1], 'created_at' => now(), 'updated_at' => now()]);
    }
}
$idProduk = DB::table('produk')->whereIn('nama_produk', ['Spanduk Outdoor', 'Jersey Sublim', 'Banner Indoor'])->pluck('id_produk', 'nama_produk');

// Nota hari ini + SPK sintetis
$tglOrder = now()->format('Y-m-d H:i:s');
$makeTrx = function (string $nomor, string $telKonsumen, string $idProdukKey, float $total, int $qty, string $statusSpk, bool $urgent = false) use ($idProduk, $konsumenMap, $tglOrder, $user) {
    $exists = DB::table('transaksi')->where('nomor_nota', $nomor)->value('id_transaksi');
    if ($exists) {
        DB::table('transaksi_detail')->where('id_transaksi', $exists)->delete();
        DB::table('transaksi')->where('id_transaksi', $exists)->delete();
    }
    $idT = DB::table('transaksi')->insertGetId([
        'nomor_nota' => $nomor,
        'tipe_order' => 'Reguler',
        'id_konsumen' => $konsumenMap[$telKonsumen],
        'id_user_kasir' => $user->id_user,
        'tgl_order' => $tglOrder,
        'total_bayar' => $total,
        'status_pembayaran' => 'Lunas',
        'status_nota' => 'Aktif',
        'status_order' => 'Diterima Kasir',
        'status_collecting' => 'Belum Diambil',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('transaksi_detail')->insert([
        'id_transaksi' => $idT,
        'id_produk' => $idProduk[$idProdukKey],
        'nama_file' => strtoupper(Str::random(8)) . '.cdr',
        'p_ukuran' => 120,
        'l_ukuran' => 300,
        'qty' => $qty,
        'qty_cetak' => $qty,
        'subtotal' => $total,
        'status_spk' => $statusSpk,
        'status_urgent' => $urgent ? 1 : 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
};
$makeTrx('ORG-DEMO-0001', '0811000111', 'Spanduk Outdoor', 350000, 2, 'Cetak', true);
$makeTrx('ORG-DEMO-0002', '0811000222', 'Jersey Sublim', 450000, 12, 'Cetak');
$makeTrx('ORG-DEMO-0003', '0811000333', 'Banner Indoor', 200000, 1, 'Desain');
$makeTrx('ORG-DEMO-0004', '0811000111', 'Spanduk Outdoor', 275000, 1, 'Antre');

echo "demo ready\n";

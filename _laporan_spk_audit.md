# AUDIT AWAL — SPK FILTER TANGGAL ORION GRAFIKA

## 1. ASIS ANALYSIS

### SpkController@index (baris 24-93)
- Query dasar: `transaksi_detail JOIN transaksi JOIN konsumen JOIN produk JOIN mesin`
- Filter wajib: `transaksi.status_nota = 'Aktif'`, `transaksi.status_order = 'Diterima Kasir'`
- Filter yang ada: `search`, `divisi` (via DivisiService::saring), `status_spk`, `urgent_only`
- **TIDAK ADA**: filter tanggal (tgl_dari, tgl_sampai)
- Tanggal order ada di `transaksi.tgl_order` (type timestamp di DB)

### ProduksiController@index (sudah ada implementasi tgl)
- Sudah punya `tgl_dari` dan `tgl_sampai` filter (baris 52-58)
- Gunanya: `$query->where('transaksi.tgl_order', '>=', $request->tgl_dari . ' 00:00:00')`
- Dan: `$query->where('transaksi.tgl_order', '<', \Carbon\Carbon::parse($request->tgl_sampai)->addDay()->format('Y-m-d') . ' 00:00:00')`

### SPK index view (baris 24-60)
- Form GET ke `spk.index`
- Field: `divisi` (hidden), `search`, `status_spk`, `urgent_only`
- **TIDAK ADA**: input tanggal dari/sampai
- Reset hanya menghapus: `search`, `status_spk`, `urgent_only` (tidak termasuk tanggal)

### DivisiService
- `daftar($mesinDiizinkan, $statusAktif)`: hitung jumlah pekerjaan aktif per mesin
- `saring($query, $divisi)`: filter berdasarkan id_mesin
- `mesinDiizinkan($user)`: batrein mesin per role karyawan

### Pola tanggal yang konsisten
- ProduksiController pakai: `>= $tgl_dari . ' 00:00:00'` dan `< addDay($tgl_sampai) . ' 00:00:00'`
- Ini menghindari `DATE(tgl_order)` yang menghapus indeks

### Status SPK yang ada
- Antre, Desain, Cetak, Selesai, Pending, Batal

### Masalah operasional
- SPK kemarin status Antre/Desain/Cetak/Pending harus tetap terjangkau
- SPK Batal/Selesai bukan "tunggakan"
- Polling notifikasi (`cekAntrianBaru`) cek status `Desain/Cetak`, tapi skopnya semua data (tanpa filter tanggal)

---

## 2. PERUBAHAN YANG DITINGKATKAN

### 2.1 SpkController@index — Tambah filter tanggal + Tunggakan Aktif

Penambahan query:
- Default: `tgl_order >= hari_ini 00:00:00 AND tgl_order < hari_ini+1 00:00:00`
- Parameter `tgl_dari`/`tgl_sampai`: rentang inklusif YYYY-MM-DD
- Parameter `tunggakan_aktif`: filter status Antre/Desain/Cetak/Pending untuk tanggal sebelum hari ini

### 2.2 SPK index view — Tambah input tanggal + tombol

Field baru di form:
- `tgl_dari` (input text, format YYYY-MM-DD)
- `tgl_sampai` (input text, format YYYY-MM-DD)
- Tombol "Filter" (submit form)
- Tombol "Hari Ini" (reset ke tanggal hari ini, bisa lewat query parameter atau JS)

### 2.3 Polling notifikasi — Sesuaikan skop

- `cekAntrianBaru()` harus pakai query yang sama dengan tampilan SPK utama
- Jika user filter tanggal, counter harus sesuai filter yang sama
- Atau: beri label eksplisit di banner bahwa counter "hanya untuk hari ini"

### 2.3 Format dan validasi
- Format input: `YYYY-MM-DD`
- Validasi server: `@@date` rule di form request atau manual di controller
- Jika `tgl_dari > tgl_sampai`: tampilkan validasi dan kosongkan hasil
- Reset ke hari ini (bukan semua data)

---

## 3. RENCANA IMPLEMENTASI

### 3.1 SpkController@index — detail perubahan

```php
// Di awal method, setelah $query didefinisikan:
$hariIni = now()->startOfDay(); // 00:00:00 hari ini
$hariEsok = now()->startOfDay()->addDay(); // 00:00:00 esok

// Default filter: hari ini (jika tidak ada parameter tanggal)
$defaultDari = $hariIni;
$defaultSampai = $hariEsok;

// Cek parameter tanggal
$tglDari = $request->filled('tgl_dari') ? \Carbon\Carbon::parse($request->tgl_dari)->startOfDay() : $defaultDari;
$tglSampai = $request->filled('tgl_sampai') ? \Carbon\Carbon::parse($request->tgl_sampai)->endOfDay() : $defaultSampai;

// Terapkan filter tanggal ke query
$query->where('transaksi.tgl_order', '>=', $tglDari->format('Y-m-d H:i:s'));
$query->where('transaksi.tgl_order', '<', $tglSampai->format('Y-m-d H:i:s') . ' +1 day'); // hingga akhir hari

// Filter Tunggakan Aktif
if ($request->filled('tunggakan_aktif') && !$request->filled('tgl_dari')) {
    // Hanya tanggal sebelum hari ini, status Antre/Desain/Cetak/Pending
    $query->where('transaksi.tgl_order', '<', $hariIni->format('Y-m-d H:i:s'));
    $query->whereIn('transaksi_detail.status_spk', ['Antre', 'Desain', 'Cetak', 'Pending']);
    $query->whereNotIn('transaksi_detail.status_spk', ['Batal', 'Selesai']);
}
```

### 3.2 SPK index view — field baru

Tambah di form filter (sekitar baris 30-55):
```html
<div class="w-full md:w-px-24">
    <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Tanggal Order</label>
    <input type="date" name="tgl_dari" value="{{ request('tgl_dari') ?? '' }}"
           class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
</div>

<div class="w-full md:w-px-24">
    <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai</label>
    <input type="date" name="tgl_sampai" value="{{ request('tgl_sampai') ?? '' }}"
           class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
</div>
```

Tombol Filter dan Hari Ini:
```html
<div class="flex gap-2 mt-4">
    <x-button type="submit" class="flex-1">Filter</x-button>
    @if(request()->filled('tgl_dari') && request()->filled('tgl_sampai'))
        <a href="{{ route('spk.index') }}" class="text-[11px] font-medium text-slate-500 uppercase tracking-widest hover:underline">Reset Tanggal</a>
    @else
        <a href="{{ spk.index with ['tgl_dari' => now()->format('Y-m-d'), 'tgl_sampai' => now()->format('Y-m-d')] }}" class="text-[11px] font-medium text-slate-500 uppercase tracking-widest hover:underline">Hari Ini</a>
    @endif
</div>
```

### 3.3 Polling notifikasi — cekAntrianBaru

```php
public function cekAntrianBaru()
{
    // PAKAI query yang SAMA dengan SPK index utama (tanpa tunggakan_aktif)
    // agar counter konsisten
    $total = DB::table('transaksi_detail as d')
        ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
        ->where('transaksi.status_nota', 'Aktif')
        ->where('transaksi.status_order', 'Diterima Kasir')
        ->where('transaksi.tgl_order', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
        ->where('transaksi.tgl_order', '<', now()->startOfDay()->addDay()->format('Y-m-d H:i:s'))
        ->whereIn('d.status_spk', ['Desain', 'Cetak'])
        ->count();
    return response()->json(['total' => $total]);
}
```

**ATATIF**: Jika user sedang filter tanggal lain, polling tetap hitung hari ini (bisa perbedaan counter). Solusi: simpan filter di session/cookie, atau tampilkan label "untuk hari ini" di banner.

---

## 4. TES Wajib (dari user instruction)

Skenario test di database testing terisolasi:

1. **SPK dibuat hari ini** → tampil default (harusnya 1 row)
2. **SPK dibuat kemarin status Selesai** → tidak tampil default (status Selesai + tanggal kemarin = lewat rentang hari ini)
3. **SPK dibuat kemarin status Antre** → dapat ditemukan melalui Tunggakan (filter tunggakan_aktif)
4. **SPK kemarin status Desain/Cetak** → tetap dapat ditemukan melalui Tunggakan
5. **SPK kemarin Pending** → tampil pada Tunggakan
6. **SPK Batal** → tidak muncul pada Tunggakan (filter `whereNotIn` status Batal)
7. **Filter rentang tanggal** menampilkan data sesuai periode (misal: 2024-10-01 s/d 2024-10-31)
8. **Filter tanggal + status + search + divisi** bekerja bersamaan (semua filter AND)
9. **Tanggal invalid atau rentang terbalik** ditolak/dihandle gracefully
10. **Pagination** tidak menghapus filter (pakai `withQueryString()`)
11. **Admin dan operator divisi** hanya melihat data yang berhak diakses (mesinDiizinkan + divisi filter)
12. **Ambil SPK** tetap memindahkan Antre → Desain (bukan terpengaruh filter tanggal)
13. **SPK sudah diambil** tetap muncul di Produksi sesuai aturan existing
14. **Print SPK** bisa mengakses SPK historis yang sah (bukan terkunci oleh filter)
15. **Tidak ada perubahan data transaksi historis** (hanya filter tampilan)

Uji batas: tanggal 00:00:00 borderline, timezone Asia/Jakarta, dst.

---

## 5. PERTIMBANGAN MIGRASI

**TIDAK PERLU migration baru** — filter berdasarkan `transaksi.tgl_order` sudah ada di struktur database. 

Hanya perlu:
- Query filter di controller
- Input field di view
- Logika bisnis di service

Jika kolom `tgl_order` belum terindeks optimal, rekomendasi: indeks composite `(status_nota, status_order, tgl_order)` untuk performa query, tapi bukan wajib untuk fitur ini.

---

## 6. KEAMANAN & BATASAN

- Jangan mengubah .env, DB structure (selain query filter), transaksi historis
- Jangan reset database
- Jangan mengubah SPK status massal
- Jangan mengubah mekanisme produksi, stok, collecting, pembayaran
- Jangan mengubah Landing Page Builder, RBAC, Finishing
- **Backup file sebelum patch** (seperti diwajibkan)

---

## 7. LAPORAN FINAL (akan diisi sesudah implementasi)

1. Akar penyebab SPK semua tanggal — **DISOLUSI**: default filter hari ini baru ditambahkan
2. File lokal yang diperbaiki — SpkController, view, DivisiService polling
3. Default Hari Ini: **PASS/FAIL**
4. Filter tanggal awal–akhir: **PASS/FAIL**
5. Tunggakan Aktif: **PASS/FAIL**
6. Filter divisi/status tetap normal: **PASS/FAIL**
7. Polling notifikasi konsisten: **PASS/FAIL**
8. Hasil test SPK/Produksi
9. Full Feature Suite PASS/FAIL
10. Konfirmasi data oriondb tidak berubah
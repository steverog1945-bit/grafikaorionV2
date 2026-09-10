<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BahanBaku;
use App\Models\Kategori;
use App\Models\Mesin;
use App\Models\Satuan;
use App\Models\Merk;
use App\Models\JenisBahan;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        // Gunakan Eager Loading untuk semua relasi termasuk satuan ganda
        $query = BahanBaku::with(['kategori', 'mesin', 'satuanStok', 'satuanHitung', 'merk', 'jenisBahan']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_bahan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_bahan', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        // Default cuma tampilkan yang aktif - bahan nonaktif (varian lama
        // yang sudah berhenti dipakai) tetap ada di DB & riwayatnya utuh,
        // cuma disembunyikan dari daftar kerja sehari-hari kecuali diminta
        // eksplisit lewat filter.
        $statusFilter = $request->get('status', 'aktif');
        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        $sortBy = $request->get('sort', 'created_at');
        if ($sortBy == 'stok') {
            $query->orderBy('stok', 'asc');
        } elseif ($sortBy == 'nama_bahan') {
            $query->orderBy('nama_bahan', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $bahan = $query->paginate(10);
        $kategori = Kategori::all();
        $mesin = Mesin::all();
        $satuan = Satuan::all();
        $merk = Merk::orderBy('nama_merk')->get();
        $jenisBahan = JenisBahan::orderBy('nama_jenis')->get();

        return view('master/bahan.index', compact('bahan', 'kategori', 'mesin', 'satuan', 'merk', 'jenisBahan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan'        => 'required',
            'kode_bahan'        => 'required|unique:bahan_baku,kode_bahan',
            'id_mesin'          => 'required',
            'id_kategori'       => 'required',
            'id_satuan_stok'    => 'required',
            'id_satuan_hitung'  => 'required',
            'nilai_konversi'    => 'required|numeric|min:0.0001',
            'stok'              => 'required|numeric|min:0',
            'stok_minimal'      => 'required|numeric',
            'id_merk'           => 'nullable|exists:merk,id_merk',
            'id_jenis_bahan'    => 'nullable|exists:jenis_bahan,id_jenis_bahan',
            'lebar_roll'        => 'nullable|numeric|min:0',
            'panjang_roll'      => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // satuan_stok (kolom string legacy) diturunkan otomatis dari nama
                // satuan yang dipilih di id_satuan_stok - form ini tidak pernah
                // mengirim field teks terpisah untuk itu, jadi sebelumnya selalu
                // tersimpan '-' untuk SEMUA bahan baru (lihat Paper Sublime,
                // id_bahan 13, yang persis menunjukkan gejala ini).
                $namaSatuanStok = Satuan::find($request->id_satuan_stok)->nama_satuan ?? '-';

                $bahan = BahanBaku::create([
                    'nama_bahan'       => $request->nama_bahan,
                    'kode_bahan'       => $request->kode_bahan,
                    'id_mesin'         => $request->id_mesin,
                    'id_kategori'      => $request->id_kategori,
                    'id_satuan_stok'   => $request->id_satuan_stok,
                    'id_satuan_hitung' => $request->id_satuan_hitung,
                    'nilai_konversi'   => $request->nilai_konversi,
                    'lebar_roll'       => $request->lebar_roll ?: null,
                    'panjang_roll'     => $request->panjang_roll ?: null,
                    'stok'             => $request->stok,
                    'stok_minimal'     => $request->stok_minimal,
                    'satuan_stok'      => $namaSatuanStok,
                    'id_merk'          => $request->id_merk ?: null,
                    'id_jenis_bahan'   => $request->id_jenis_bahan ?: null,
                    'status'           => 'aktif'
                ]);

                // Stok awal saat bahan baru didaftarkan tetap harus tercatat
                // di kartu_stok - kalau tidak, angka stok pertama bahan ini
                // muncul begitu saja tanpa jejak (audit trail bolong sejak
                // hari pertama bahan itu ada).
                if ((float) $request->stok > 0) {
                    DB::table('kartu_stok')->insert([
                        'id_bahan'     => $bahan->id_bahan,
                        'tanggal'      => now(),
                        'tipe'         => 'IN',
                        'qty'          => $request->stok,
                        'stok_sebelum' => 0,
                        'stok_sesudah' => $request->stok,
                        'referensi'    => 'STOK-AWAL',
                        'keterangan'   => 'Stok awal saat pendaftaran bahan baru',
                        'created_by'   => auth()->id(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                return back()->with('success', 'Bahan baku dengan logika konversi berhasil disimpan!');
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id_bahan)
    {
        $request->validate([
            'nama_bahan'        => 'required',
            'kode_bahan'        => 'required|unique:bahan_baku,kode_bahan,'.$id_bahan.',id_bahan',
            'id_satuan_stok'    => 'required',
            'id_satuan_hitung'  => 'required',
            'nilai_konversi'    => 'required|numeric|min:0.0001',
            'stok_minimal'      => 'required|numeric',
            'id_merk'           => 'nullable|exists:merk,id_merk',
            'id_jenis_bahan'    => 'nullable|exists:jenis_bahan,id_jenis_bahan',
            'lebar_roll'        => 'nullable|numeric|min:0',
            'panjang_roll'      => 'nullable|numeric|min:0',
        ]);

        $bahan = BahanBaku::findOrFail($id_bahan);

        // 'stok' SENGAJA tidak lagi diterima di sini (sebelumnya ada di
        // validasi & fillable, jadi form edit bahan biasa - buat perbaiki
        // nama/kategori/dll - bisa diam-diam mengubah angka stok tanpa
        // jejak apa pun di kartu_stok. Field "Stok Awal" di modal sekarang
        // dikunci saat mode edit (lihat editBahan() di view); satu-satunya
        // jalur resmi mengubah stok bahan yang SUDAH ada adalah Kartu Stok
        // > Penyesuaian, yang mewajibkan alasan dan selalu mencatat kartu_stok.
        $data = $request->only([
            'nama_bahan', 'kode_bahan', 'id_mesin', 'id_kategori',
            'stok_minimal', 'id_satuan_stok', 'id_satuan_hitung', 'nilai_konversi',
        ]);
        $data['id_merk'] = $request->id_merk ?: null;
        $data['id_jenis_bahan'] = $request->id_jenis_bahan ?: null;
        $data['lebar_roll'] = $request->lebar_roll ?: null;
        $data['panjang_roll'] = $request->panjang_roll ?: null;
        $data['satuan_stok'] = Satuan::find($request->id_satuan_stok)->nama_satuan ?? $bahan->satuan_stok;

        $bahan->update($data);

        // back() bukan redirect()->route() supaya query string halaman saat
        // ini (?page=2&search=...) ikut terbawa - edit banyak bahan dari
        // halaman 2+ dulu selalu terlempar balik ke halaman 1 tiap simpan.
        return back()->with('success', 'Data bahan & logika konversi diperbarui!');
    }

    public function destroy($id)
    {
        try {
            BahanBaku::destroy($id);
            return back()->with('success', 'Bahan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal hapus! Data masih terikat dengan transaksi.');
        }
    }

    /**
     * Alternatif untuk hapus permanen: bahan yang sudah punya riwayat
     * transaksi tidak pernah bisa dihapus (FK RESTRICT, sengaja begitu -
     * lihat migrasi 310000). Nonaktifkan menyembunyikan bahan dari
     * dropdown pemilihan di transaksi baru (Kedatangan Bahan, BOM Produk)
     * tanpa kehilangan histori kartu_stok/BOM yang sudah ada - berguna
     * untuk varian lebar roll yang berhenti dipakai tapi tetap perlu
     * muncul di laporan lama.
     */
    public function toggleStatus($id)
    {
        $bahan = BahanBaku::findOrFail($id);
        $bahan->update([
            'status' => $bahan->status === 'aktif' ? 'nonaktif' : 'aktif',
        ]);

        return back()->with('success', $bahan->status === 'aktif'
            ? 'Bahan diaktifkan kembali.'
            : 'Bahan dinonaktifkan - tidak akan muncul lagi di pilihan transaksi baru.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids) {
            return back()->with('error', 'Tidak ada data dipilih.');
        }

        // Setiap bahan dihapus satu per satu (bukan satu transaksi gabungan)
        // supaya bahan yang masih terikat riwayat transaksi (FK constraint)
        // tidak ikut menggagalkan penghapusan bahan lain yang sebenarnya aman.
        $berhasil = 0;
        $gagal = 0;

        foreach ($ids as $id) {
            try {
                BahanBaku::destroy($id);
                $berhasil++;
            } catch (\Exception $e) {
                $gagal++;
            }
        }

        $pesan = "{$berhasil} bahan berhasil dihapus.";
        if ($gagal > 0) {
            $pesan .= " {$gagal} bahan gagal dihapus karena masih terikat riwayat transaksi.";
        }

        return back()->with($berhasil > 0 ? 'success' : 'error', $pesan);
    }
}
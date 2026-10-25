# Mobile Visual Final Polish — Master Bahan

## Status

Perbaikan kartu Master Bahan selesai pada branch `fix/mobile-visual-final-polish`. Perubahan ini hanya presentasional. Tidak ada runtime Laravel, database, produksi, merge, atau deployment yang digunakan.

## Branch, commit, dan PR

- Branch: `fix/mobile-visual-final-polish`
- Base PR: `main` pada `aee1d5d01b0de3c3c6fd6a0fe33545e6f6b082f6`
- Commit implementasi terbaru: `9a0c56da463e4c004e87c0635541158891331f03`
- PR #15: [Draft — fix(mobile): compact Master Bahan cards](https://github.com/steverog1945-bit/percetakan-app/pull/15)
- PR diperiksa sebagai OPEN/Draft sebelum push commit baru. Push non-force dan verifikasi remote masih menjadi langkah delivery.

## Perbaikan dan cakupan

- Konflik minimum-width tabel global dinetralkan khusus daftar Master Bahan pada breakpoint mobile; header kolom desktop disembunyikan dan setiap baris memakai grid kartu.
- Spacing kartu dipadatkan; identitas, spesifikasi, stok, harga rata-rata, harga beli terakhir, selisih, checkbox, nomor, dan menu aksi tetap bersumber dari sel yang sama.
- Label dan nilai stok diberi ruang di bawah menu aksi 44×44px pada sudut kartu, menghilangkan tumpang tindih yang terlihat pada preview 390px.
- Search dan ringkasan Filter tetap satu baris pada preview 320–767px; panel filter terbuka tidak melampaui lebar dokumen.
- Floating bottom navigation tetap memakai CSS yang ada. Selector CSS menyembunyikannya saat `body.mobile-menu-open`; interaksi drawer/modal ERP sesungguhnya tidak dijalankan.
- Tidak ada perubahan controller, model, route, transaksi, stok, RBAC, perhitungan, service worker, atau manifest.

## Perbandingan visual dengan referensi owner

Yang sejalan: komposisi kartu material horizontal, identitas dominan, metrik di kolom kanan, tombol aksi di sudut, search/filter ringkas, dan navigasi bawah berbentuk kapsul solid.

Perbedaan nyata: referensi menampilkan status bar/topbar ORION lengkap, chip status beserta hitungan, dan foto produk. Preview ini hanya mengisolasi area halaman Master Bahan; Blade yang tersedia tidak menyediakan chip hitungan maupun foto pada daftar ini, sehingga konten tersebut tidak ditambahkan atau direka. Monogram dan placeholder generik dipakai untuk preview statis. Pada lebar 320px, teks harga dapat membungkus lebih banyak dibanding referensi.

## File pada branch terhadap `origin/main`

- `resources/css/mobile-bahan.css` — layout kartu mobile dan pencegahan overlap stok/menu.
- `resources/views/master/bahan/index.blade.php` — perubahan sebelumnya di branch PR untuk disclosure aksi mobile; tidak diubah dalam commit perbaikan terbaru.
- `docs/reviews/MOBILE_VISUAL_FIDELITY_FINAL.md` — laporan ini.
- `docs/reviews/RELEASE_READINESS.md` — checklist kesiapan rilis.

Dalam commit implementasi terbaru hanya `resources/css/mobile-bahan.css` yang berubah. Blade, form GET/POST, CSRF, sembilan sel data, callback edit/status/delete/koreksi harga, dan urutan/record tidak diubah pada commit ini.

## Tes dan bukti visual

| Pemeriksaan | Status | Batasan |
|---|---|---|
| Vite production frontend build (`npm run build`) | PASS | Hanya bundle frontend; bukan boot Laravel. |
| `php -l` untuk Blade Master Bahan dan komponen tabel | PASS | Syntax PHP saja, bukan render Blade. |
| `git diff --check` | PASS | Validasi whitespace. |
| CSS terkompilasi di browser canvas, 320/360/390/430/767px | PASS | Preview statis/source-derived; tidak ada horizontal overflow, heading tabel tersembunyi, row menjadi grid, search/filter sebaris. |
| Filter dibuka pada viewport mobile | PASS | Lebar panel dan scroll width tidak melampaui viewport dalam preview statis. |
| Target sentuh menu aksi, search, filter | PASS | 44×44px, 52px tinggi, 52px tinggi. |
| Fallback desktop 768/1024px | PASS | Bundle mempertahankan table, table-row, header, min-width global; bukan screenshot ERP live. |
| Modal, Select2, drawer interaktif, runtime Laravel, data ERP | NOT TESTED | Tidak tersedia/ tidak dijalankan untuk tugas ini. |
| Screenshot ERP asli | NOT AVAILABLE | Screenshot browser yang tersedia adalah preview statis dengan placeholder, bukan halaman Laravel atau data ERP. |

Screenshot preview statis pada 320px dan 390px tersedia di browser canvas pada sesi ini. Preview diberi label **STATIC SOURCE-DERIVED PREVIEW — GENERIC PLACEHOLDERS — NOT AN ERP SCREENSHOT**. Gambar tidak disimpan sebagai screenshot repo. Batas browser preview menggunakan scrollbar desktop; safe-area perangkat iPhone tidak dapat dibuktikan dari preview ini.

## Risiko dan dampak

- Render Blade sesungguhnya, data dengan nama/harga panjang, drawer/modal aktif, Select2, dan safe-area iPhone perlu smoke-test setelah aplikasi tersedia di lingkungan uji aman.
- Tidak ada temuan keamanan baru yang diverifikasi: tidak ada perubahan pada jalur authorization atau endpoint. Audit IDOR bukan bagian dari commit CSS ini dan tidak dinyatakan lulus.
- Dampak produksi: **ZERO**. Tidak ada akses ke produksi, `.env`, database, migration, server, layanan Windows, merge, atau deployment.

## Pola reusable dan prioritas lanjutan

Pola yang dapat ditinjau owner sebelum dipakai di modul lain: filter mobile ringkas dengan panel kolaps, kartu dari baris/sel yang sama tanpa duplikasi data, label sel berbasis `data-label`, aksi dalam disclosure 44×44px, token tema yang ada, serta bottom-navigation dengan safe-area. Prioritas modul berikutnya tetap: **POS → SPK → Produksi → Collecting → Jersey → Keuangan → Laporan → Master lainnya**. Tidak ada modul lanjutan yang diimplementasikan dalam PR #15.

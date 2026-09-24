# Panduan Penggunaan ORION GRAFIKA (ERP Percetakan)

Panduan ini menjelaskan alur kerja aplikasi per peran. Nama menu mengikuti sidebar. Menu di sidebar dikelompokkan dalam grup yang bisa dibuka/ditutup (Produksi & Logistik, Relasi Bisnis, Finance & HR, Laporan, Akuntansi, Order Jersey, System Control); grup berisi halaman yang sedang dibuka otomatis terbuka.

## 1. Peran dan akses

| Peran | Tugas utama | Menu yang tersedia |
|---|---|---|
| Administrator | Mengelola semua, menyetujui pengajuan, menutup periode, backup | Semua menu |
| Kasir | Input order, terima pembayaran, kas harian, serah barang | Input Order, Penjualan Barang, Kasir, Collecting, Deposit Konsumen, Data Konsumen, Order Jersey, Laporan Kas, Laporan Keuangan |
| Operator Desain | Input order dan mengerjakan desain | Input Order, Produksi, Collecting, Data Konsumen, Order Jersey (input), Laporan Produksi |
| Operator Produksi | Mengerjakan antrean cetak | SPK, Produksi, Finishing Produk |
| Gudang | Barang masuk, stok, permintaan pembelian | Bahan Baku, Kedatangan Bahan, Gudang, Stock Opname, Purchase Request, Laporan Stok Bahan Baku |

Hak akses tiap peran diatur Administrator di System Control > Setting Akses. Membuka halaman di luar hak akses menghasilkan pesan 403.

## 2. Aturan penting yang berlaku di seluruh aplikasi

1. **Tidak ada penghapusan langsung untuk data keuangan.** Membatalkan nota, faktur pembelian, pembayaran hutang, pembayaran/order Jersey, dan catatan Kas Kecil hanya bisa **diajukan**. Administrator memutuskan di menu **Approval Workflow**. Saat disetujui, jurnal akuntansi dibalik dengan entri kontra; jurnal asli tidak pernah dihapus.
2. **Jurnal akuntansi dibuat otomatis** dari transaksi (penjualan, pembayaran, pembelian, kas kecil, deposit). Anda tidak menginput jurnal manual.
3. **Periode yang sudah ditutup terkunci.** Setelah Administrator menutup suatu bulan di Akuntansi > Accounting Period, transaksi yang menghasilkan jurnal bertanggal bulan itu ditolak sampai periode dibuka lagi.
4. **Semua perubahan penting tercatat** di System Control > Log Aktivitas (siapa, kapan, sebelum dan sesudah).
5. **Nomor dokumen dibuat otomatis** oleh server (INV-, PR-, PO-, SO- diikuti tanggal dan urutan). Jangan mengetik nomor sendiri.
6. **Halaman daftar dan dropdown memuat data terbaru saat dibuka.** Jika ada data baru dari halaman lain, buka dropdown lagi; tidak perlu memuat ulang halaman.

## 3. Persiapan awal (sekali, sebelum dipakai massal)

Lakukan berurutan oleh Administrator:

1. **Master data:** Kategori Bahan, Jenis Bahan, Merk, Satuan & Konversi, Aset Mesin, Gudang, Supplier, Bank Perusahaan, Golongan Konsumen, Data Karyawan.
2. **Bahan Baku:** isi stok awal, stok minimal, dan satuan hitung.
3. **Produk & BOM:** tentukan bahan baku yang dipakai tiap produk (dasar HPP dan pengurangan stok otomatis), lalu Finishing Produk, Jasa, Price List & Level, dan Harga Promo bila ada.
4. **Pengguna dan akses:** Data Karyawan, Manajemen User, lalu Setting Akses per peran.
5. **Pengaturan Aplikasi:** nama, alamat, format nota, prefix nomor nota, pembulatan, libur kas hari Minggu.
6. **Saldo awal akuntansi:** jalankan `php artisan akuntansi:saldo-awal` sekali (Kas, Bank, Piutang, Persediaan, Hutang). Perintah ini menolak jalan jika jurnal sudah berisi transaksi.
7. **Reset data uji** dilakukan sebelum go-live agar transaksi percobaan tidak ikut ke data nyata.

## 4. Alur Pembelian bahan baku

```
Purchase Request -> Approval -> Purchase Order -> Kedatangan Bahan -> Pembelian (faktur) -> Bayar Hutang
```

1. **Gudang membuat Purchase Request** (Produksi & Logistik > Purchase Request > baru): pilih bahan dan qty, simpan sebagai Draft, lalu **Ajukan untuk Persetujuan**. Draft masih bisa diedit atau dihapus.
2. **Administrator menyetujui** di Approval Workflow. PR yang ditolak berstatus Ditolak dan tidak diproses.
3. **Pembelian membuat Purchase Order:** di daftar PR, PR berstatus Disetujui punya tombol **Buat PO**. Pilih supplier, isi harga, diskon, pajak (%), ongkir, termin, dan estimasi kedatangan. Total dihitung server. Simpan Draft, lalu **Terbitkan ke Supplier** (setelah itu tidak bisa diubah). PO juga bisa dibuat langsung tanpa PR.
4. **Barang datang:** di halaman PO berstatus Dipesan, isi No. Surat Jalan dan tanggal, lalu **Catat Kedatangan**. Sistem membuat Kedatangan Bahan (Draft) berisi sisa qty yang belum diterima. Buka kedatangan itu, sesuaikan qty fisik bila berbeda, lalu **validasi**. Saat divalidasi, stok bertambah dan tercatat di Kartu Stok; status PO menjadi Diterima Sebagian atau Selesai otomatis. Kedatangan tanpa PO (alur lama) tetap bisa dibuat.
5. **Faktur:** menu Pembelian Bahan > buat faktur dari kedatangan yang sudah divalidasi. Pilih **Cash** (langsung lunas) atau **Tempo** (menjadi hutang dengan jatuh tempo). No. faktur tidak bisa diubah setelah disimpan.
6. **Bayar hutang:** di daftar Pembelian Bahan, tombol Bayar Hutang. Isi nominal (default sisa hutang; boleh dicicil) dan metode (Tunai memotong Kas, Transfer memotong Bank). Faktur Lunas saat sisa nol. Riwayat pembayaran ada di halaman detail faktur.
7. **Salah bayar atau faktur salah?** Di detail faktur pilih **Ajukan Void** untuk satu pembayaran, atau tombol hapus di daftar untuk membatalkan seluruh faktur. Faktur yang masih punya pembayaran aktif hanya bisa dibatalkan setelah pembayarannya di-void.
8. **Pantau hutang:** Laporan > Laporan Hutang Supplier menampilkan sisa hutang per supplier menurut umur keterlambatan.

Purchase Order berstatus Dipesan/Draft bisa dibatalkan selama belum ada barang yang diterima.

## 5. Alur Stok

- **Kartu Stok:** riwayat semua mutasi stok per bahan (masuk, keluar, penyesuaian).
- **Stock Opname** (Produksi & Logistik > Stock Opname), dilakukan berkala oleh Gudang:
  1. Buat Stock Opname baru; semua bahan aktif otomatis masuk dengan snapshot stok sistem.
  2. Isi **stok fisik** hasil hitung. Bahan yang selisih wajib diberi alasan.
  3. **Ajukan untuk Persetujuan**. Stok belum berubah.
  4. Administrator menyetujui di Approval Workflow; barulah stok disesuaikan ke hasil hitung fisik dan tercatat sebagai penyesuaian di Kartu Stok.
- **Waste Management** (Produksi & Logistik > Waste Management): catat susut bahan di luar pemakaian normal BOM. **Normal** (susut wajar, mis. sisa potongan) langsung mengurangi stok dan dijurnal sebagai beban. **Abnormal** (rusak, salah potong, hilang) hanya diajukan; stok baru berkurang setelah Administrator menyetujui. Pilih item order terkait bila ada, supaya biayanya masuk Job Costing order itu.
- **Penyesuaian stok manual satu bahan** (Kartu Stok): jalur cepat untuk mengeluarkan atau merevisi stok satu bahan. Sama seperti Stock Opname, ini hanya **mengajukan**; stok baru berubah setelah Administrator menyetujui, dan persetujuan gagal aman kalau stok sudah tidak cukup.
- Stok bahan baku berkurang otomatis saat **nota dibuat** (Input Order), sesuai BOM produk, dan kembali otomatis saat nota atau SPK dibatalkan. Bahan yang terpakai untuk rework dikeluarkan terpisah lewat QC & Rework (bagian 6).

## 6. Alur Penjualan dan Produksi

```
Input Order -> SPK -> Produksi -> Pembayaran di Kasir -> Collecting (serah barang)
```

0. **Quotation (opsional, Finance & HR > Quotation):** untuk pelanggan yang bertanya harga dulu. Buat penawaran (pelanggan terdaftar atau prospek), pilih produk/jasa, ukuran, qty; harga dihitung otomatis dari Price List golongan pelanggan (sama dengan Input Order) dan bisa ditimpa per baris, ditambah finishing, desain, dan diskon. Cetak/simpan PDF atau kirim lewat WhatsApp, lalu **Tandai Terkirim**. Catat tanggapan pelanggan (**Setuju/Menolak**). Penawaran lewat masa berlaku otomatis tampil **Kedaluwarsa** dan tidak bisa disetujui atau dijadikan order. Untuk penawaran yang Terkirim/Disetujui, tombol **Buat Order dari Penawaran** membuka Input Order dengan rincian penawaran sebagai acuan di bagian atas layar; **item tetap diinput operator** sesuai penawaran. Begitu order tersimpan, penawaran berstatus **Menjadi Order** dan tertaut ke nota.
1. **Input Order** (Kasir atau Operator Desain): pilih atau tambah konsumen, pilih produk/jasa/finishing, isi ukuran dan qty, tentukan urgensi. Nomor nota dibuat server. Nota dapat berstatus Belum Bayar, DP, atau Lunas.
2. **SPK** (Operator Produksi): antrean kerja per item, status Antre, Desain, Cetak, Selesai (atau Pending/Batal).
3. **Produksi:** operator mencatat qty hasil, menyelesaikan, atau membatalkan item.
2a. **Design Proof** (Produksi & Logistik > Design Proof): untuk item yang desainnya perlu persetujuan pelanggan. Buka item, **unggah proof** (JPG/PNG/WEBP/PDF, maks 20 MB); tiap unggahan menjadi **Revisi #1, #2, ...** dengan waktu, pengunggah, dan catatan desainer. Setelah pelanggan memutuskan (lewat WhatsApp, langsung, atau email), staf mencatat **Setuju** atau **Minta revisi** (wajib catatan) beserta kanalnya. Setelah **Disetujui, desain terkunci**: unggahan baru ditolak. Bila pelanggan berubah pikiran, klik **Ajukan Buka Revisi** dengan alasan; Administrator memutuskan di Approval Workflow, baru revisi berikutnya bisa diunggah. **Penjaga produksi bersifat opsional per item:** item yang sudah punya proof tidak bisa naik ke Cetak atau Selesai di Produksi sebelum proof terakhirnya disetujui; item yang tidak memakai proof tidak terpengaruh.
3a. **QC & Rework** (menu Produksi & Logistik > QC & Rework): periksa hasil cetak per item (status Cetak/Selesai). Isi qty diperiksa, qty reject, dan centang poin bermasalah (ukuran, warna, posisi desain, jumlah, finishing, cacat cetak). Jika ada reject, **Rework otomatis dibuka**. Klik **Proses Rework** saat pengerjaan ulang dimulai: material tambahan sesuai BOM dikeluarkan dari stok, tercatat di Kartu Stok, dan dijurnal sebagai Beban Rework & Susut Bahan (akun 5200). Unit hasil rework dapat di-QC ulang setelah rework diproses. Stok yang tidak cukup untuk rework menggagalkan proses tanpa efek samping.
4. **Kasir:** pembayaran diterima lewat menu Kasir (Monitoring Kasir): tunai, transfer bank, atau deposit konsumen; DP dan cicilan didukung. Piutang dapat dilihat di Laporan Piutang.
5. **Collecting (serah terima):** daftar pesanan siap diambil. Saat barang diserahkan, klik tanda centang dan isi **jenis serah terima** (diambil sendiri, diambil perwakilan, atau dikirim), **nama penerima** (wajib untuk perwakilan/dikirim), catatan, dan **foto bukti** (opsional). Catatan ini menjadi Delivery Proof di Berkas Order dan muncul di Timeline Pelanggan.
5a. **Berkas Order** (tombol di halaman detail nota): semua berkas satu nota dalam satu tempat, dikelompokkan: Design (thumbnail), Proof, Approved Design (dari Design Proof), Production File, QC Photo, Lainnya (diunggah di halaman ini: JPG/PNG/WEBP/PDF/ZIP/AI/PSD/CDR/EPS, maks 50 MB), dan Delivery Proof. Berkas berfungsi sebagai bukti sehingga tidak bisa dihapus dari sini.
6. **Penjualan Barang:** menjual bahan baku langsung (khusus Kasir), otomatis masuk nota dan jurnal.
7. **Notifikasi antrean:** halaman SPK, Produksi, dan Collecting menampilkan banner saat ada data baru dari divisi lain; klik banner untuk memuat ulang.

**Nota salah:**
- **Batalkan nota:** dari Riwayat Penjualan, ajukan pembatalan dengan alasan. Setelah Administrator menyetujui, nota berstatus Dibatalkan, stok dikembalikan, dan jurnal dibalik.
- **Edit nota:** ajukan permintaan edit dari Riwayat Penjualan; setelah disetujui Administrator, edit diterapkan dan jurnal dikoreksi.

**Timeline Pelanggan:** di Data Konsumen, tombol ikon garis waktu pada baris pelanggan membuka ringkasan hubungan bisnis (total omzet, sudah dibayar, piutang, saldo deposit, order terakhir, nota belum diambil) dan riwayat berurut waktu: penawaran, nota, pembayaran, deposit, dan order Jersey. Halaman ini hanya untuk dibaca; nota dan penawaran di dalamnya bisa diklik. Nota yang dibatalkan tampil merah dan tidak dihitung ke omzet/piutang.

## 7. Kas dan keuangan harian

- **Buka/Tutup Kas:** kas harian dibuka satu orang saja per hari (eksklusif). Saldo awal, transaksi tunai, dan kas manual dicatat di sesi itu. Hari Minggu libur kas jika diaktifkan di Pengaturan Aplikasi. Di akhir hari lakukan **Closing** dan cetak laporannya.
- **Kas Kecil:** catat pengeluaran kecil per kategori (Kategori Kas Kecil menentukan akun beban di jurnal). Membatalkan catatan hanya berupa pengajuan ke Approval Workflow; jurnalnya dibalik setelah disetujui.
- **Deposit Konsumen:** setoran deposit dicatat sebagai kewajiban dan dapat dipakai membayar nota.
- **Laporan:** Order, Produksi, Kas Utama, Belanja Bahan, Hutang Supplier, Piutang, Stok Bahan Baku, HPP & Margin, **Job Costing** (biaya aktual per nota = HPP material + rework + waste terkait, dibanding omzet; tenaga kerja, mesin, dan overhead belum tercatat), Order Jersey, dan Laporan Keuangan (Akuntansi).

**Dashboard Eksekutif (khusus Administrator, sidebar di bawah Dashboard):** ringkasan periode (default bulan berjalan): omzet, biaya aktual, laba kotor dan margin, omzet Jersey; saldo Kas, Bank, Piutang, dan Hutang dari buku besar; grafik omzet 6 bulan; top 5 pelanggan dan produk; peringatan (pengajuan menunggu, bahan di bawah stok minimal, PO lewat estimasi, faktur hutang jatuh tempo, nota belum lunas > 7 hari, rework menunggu); dan **Estimasi Bulan Depan** (rata-rata 3 bulan penuh, acuan kasar, bukan prediksi). **Cara membaca:** laba kotor = omzet - (HPP material + rework + waste); tenaga kerja, mesin, overhead, dan beban operasional belum tercatat sehingga bukan laba bersih, dan akurasinya bergantung pada BOM serta harga rata-rata bahan yang terisi benar.

**Laporan Profitabilitas:** laba kotor dan margin per produk/jasa, pelanggan, atau mesin/divisi untuk rentang tanggal. **Analisis Pemakaian Bahan:** stok keluar per bahan dipisah pemakaian order, rework, dan waste, dengan persentase dan nilai susut.

## 8. Order Jersey

Order Jersey (Order Jersey > Daftar Order, Master Data Jersey, Keuangan Jersey) memiliki modul sendiri untuk order, komponen, ukuran, dan pembayaran, dan **terhubung ke akuntansi**:

- Total order diakui sebagai piutang dan pendapatan di akun **Pendapatan Order Jersey (4300)**. Jika line order berubah, sistem hanya memposting selisihnya.
- Pembayaran Tunai masuk Kas; Transfer/QRIS/Lainnya masuk Bank. Pembayaran mengurangi piutang.
- **Hapus pembayaran** dan **hapus order yang sudah bernilai atau sudah ada pembayaran** hanya berupa pengajuan ke Approval Workflow (jurnal dibalik saat disetujui). Order yang punya pembayaran hanya bisa dibatalkan setelah pembayarannya di-void. Order kosong (belum bernilai) masih bisa dihapus langsung.
- Data Jersey lama (sebelum integrasi ini) tidak di-backfill ke jurnal; data uji direset sebelum go-live.

## 9. Akuntansi dan tutup buku bulanan

1. Pastikan semua transaksi bulan berjalan sudah dicatat dan kas sudah closing.
2. Periksa Laporan Keuangan dan jalankan `php artisan audit:integritas` (jurnal balance, saldo, sinkron kartu stok).
3. Administrator membuka **Akuntansi > Accounting Period**, lalu **Tutup Periode** bulan tersebut.
4. Koreksi terhadap bulan yang sudah ditutup: Administrator membuka kembali periode itu, memposting koreksi, lalu menutup lagi. Semuanya tercatat di Log Aktivitas.

## 10. Administrasi sistem (Administrator)

- **Approval Workflow:** daftar pengajuan Menunggu. Setujui untuk mengeksekusi, atau Tolak dengan alasan. Jenis pengajuan saat ini: pembatalan nota, Stock Opname, Purchase Request, pembatalan faktur pembelian, void pembayaran hutang, void pembayaran Jersey, pembatalan order Jersey, pembatalan catatan Kas Kecil, Waste Abnormal, pembukaan revisi desain yang sudah terkunci, dan penyesuaian stok manual.
- **Log Aktivitas:** telusuri perubahan data, filter menurut jenis, kejadian, dan tanggal.
- **Backup & Recovery:** backup otomatis harian, mingguan, dan bulanan (retensi 7/4/12) dan tombol backup manual. Otomatis hanya berjalan jika scheduler aktif (lihat panduan deploy). File backup di `storage/app/backups/` berisi data konsumen; simpan salinannya di lokasi lain juga.
- **Manajemen User dan Setting Akses:** buat akun per karyawan, atur menu per peran.

## 11. Hal yang belum tercakup (saat ini)

- **Stock Transfer antar gudang:** ditunda karena stok masih satu angka global dan hanya ada satu gudang aktif.

## 12. Bila ada masalah

| Gejala | Yang dilakukan |
|---|---|
| Muncul 403 | Peran Anda belum diberi akses menu itu; minta Administrator. |
| "Periode akuntansi ... sudah ditutup" | Bulan itu sudah ditutup; minta Administrator membuka periodenya bila memang perlu koreksi. |
| Tidak bisa membatalkan faktur/PO | Cek pesan: ada pembayaran aktif (void dulu) atau barang sudah diterima. |
| Tombol Bayar Hutang menolak nominal | Nominal tidak boleh melebihi sisa hutang. |
| Kas tidak bisa dibuka | Kas hanya boleh aktif satu sesi; pastikan sesi lain sudah closing. |
| Stok tidak sesuai fisik | Lakukan Stock Opname, jangan mengubah lewat database. |

# AUDIT ERPV3 → GRAFIKAORION — 18 November 2026

erp-v3 (`github.com/steverog1945-bit/erpv3`, HEAD `87ccfa0`, 13 Nov 2026) diaudit untuk mencari hal yang bisa diterapkan ke grafikaorionV2 tanpa menyeret masalahnya. **Prinsip: uji & audit dulu, baru terapkan; beban halaman tidak boleh naik.**

## A. Temuan di erpv3 yang RELEVAN (sudah / bisa diterapkan)

| # | Temuan erpv3 | Nilai | Status di grafikaorionV2 |
|---|---|---|---|
| 1 | **"Command Center" direction** (`docs/DESIGN.md` 2026-11-10): topbar 2 baris + mega menu grid (accordion mobile), kartu radius 16px, background ice + radial wash, font smoothing, fade-in/out antar halaman | Arah desain menyeluruh untuk ERP desktop-first | **Diterapkan (parsial):** tema Premium "Champagne & Ink" + **shell tanpa sidebar** → topbar baris kedua + mega menu (implementasi `_premium-bar.blade.php`, gaya premium-theme.css) |
| 2 | **Topbar 2 baris**: bar1 = utilitas (nama app, pencarian, notif, avatar), bar2 = "≡ Semua Menu" + tab modul + aksen aktif | Navigasi cepat tanpa sidebar, "big menu super minimalis" | **Diterapkan** sebagai `layouts/_premium-bar.blade.php` — gerbang izin memakai `$cekAkses` yang sama persis dengan sidebar |
| 3 | **Mega menu grid** ala Canva (panel besar dengan grid grup), tombol "Semua Menu" | Satu klik ke mana pun | **Diterapkan** — 10 grup, ~50 entri, izin identik sidebar; kasir: `/jersey/master` tersembunyi, `/jersey/keuangan` tampil (keputusan owner 18 Nov 2026) |
| 4 | **Pengaturan Topbar** (`PengaturanTopbarController`, tabel `topbar_tabs`, view `pengaturan.topbar`, migration 2026_11_12) — label/urutan/ikon tab modul bisa diatur Administrator | Fleksibilitas tanpa kode | **BELUM diterapkan** — butuh migrasi tabel baru + model + halaman pengaturan + gating izin. Direkomendasikan sebagai langkah lanjutan (opsional) |
| 5 | **Command Palette (⌘K)** sudah ada di GF2 (tombol "Cari menu" + `window.bukaPalette`) | Sama | Tidak perlu — sudah setara |
| 6 | **"Fade in/out" transisi halaman** (DESIGN.md) | Empuk tapi bukan inti ERP | Parsial ada (motion.css). Tidak dipaksakan — ERP padat interaksi; motion berlebihan memperlambat penggunaan harian |
| 7 | **Premium = fitur berbayar/paywall** (`views/premium/*`, `PengaturanTopbarController`, `premium_analisis`, dst.) — halaman showcase + penguncian fitur Insight/Enterprise | Model bisnis SaaS | **TIDAK cocok diterapkan ke grafikaorionV2** — GF2 = ERP internal percetakan, bukan produk SaaS dengan lisensi; paywall bertentangan dengan model admin-perusahaan-tunggal |

## B. Temuan yang SENGAJA TIDAK diadopsi (dengan alasan)

| # | Erpv3 | Alasan |
|---|---|---|
| 1 | Branding "Orion Premium" + paket fitur (Insight/Enterprise/Lisensi) | Produk berbeda; GF2 bukan SaaS bertingkat |
| 2 | Paywall/lock ikon pada fitur (katalog "status: Berjalan/Segera") | Sama — model bisnis berbeda |
| 3 | Warna ungu→emas hero (`#1B1437→#2E2140`) | Identitas erpv3; GF2 Premium memilih Champagne & Ink (tinta/espresso) yang koheren dengan palet Orion |
| 4 | migrasi `topbar_tabs` otomatis | Perubahan skema GF2 → harus lewat persetujuan owner + migrasi terpisah (belum dilakukan) |
| 5 | Ikon FontAwesome baru yang tidak ada di GF2 | GF2 memakai FA set yang sama; tidak menambah aset |

## C. Ukuran dampak beban halaman (GF2, diukur)

| Item | Sebelum | Sesudah | Delta |
|---|---|---|---|
| JS bundle (hash) | `app-CHoTb8S4.js` 153.63 kB (50.87 gzip) | `app-CHoTb8S4.js` 153.63 kB | **0, identik** |
| CSS bundle | 245.69 kB (39.61 gzip) | 258.58 kB (41.13 gzip) | +12.9 kB raw, **+1.52 kB gzip** (shell frame + bar2 + mega + PWA polish) |
| Request baru | — | 0 | ikon FA, vanilla JS tanpa library |
| Server markup | sidebar (~300 baris Blade) | dilewati saat Premium (menu dipindah ke bar2 + mega) | nol biaya jaringan tambahan |

Kesimpulan beban: **tidak memengaruhi load halaman secara berarti** (delta < 1.5 kB gzip, tanpa JS baru).

## D. Penerapan yang dilakukan (18 Nov 2026)

1. Tema Premium "Champagne & Ink": ramp Tailwind diremap (slate→linen hangat, indigo→perunggu/emas antik #8C6A2F, kontras tombol utama 4.97:1 ≥ WCAG AA), sidebar espresso-gold, judul serif Georgia, tabel/hover/focus/motion disesuaikan, CSS `premium-theme.css`.
2. **Shell Premium full-bleed (revisi)**: shell premium bukan lagi "kartu melayang" — topbar flush tepi-ke-tepi (547? tidak: margin 0, radius 0, border-bottom hairline, sticky top-0, tinggi 56px), logo mark tinta dengan aksen emas, nama aplikasi serif, aksi ghost tenang; **baris kedua menempel flush** (bar2 ramai edge-to-edge, tanpa kartu mengambang), mega menu jadi full-bleed 6-kolom; konten tetap center konsisten di bawah rail yang sama. PWA/mobile: baris kedua disembunyikan < 768px (bottom-nav menangani), topbar 52px & padding edge aman `env(safe-area-inset-*)`, theme-color ikut tema (premium: #1E1B17 / #F7F4EC), bottom-nav tetap.
3. `Data Master Jersey` tersembunyi dari Kasir di **semua** permukaan (sidebar klasik, mega menu, akses rute `/jersey/master*` → 403), sedangkan **input pesanan & keuangan jersey tetap tampil untuk Kasir** (keputusan owner).
4. CSS global: seluruh hardcoded teal di resources dipetakan ke `var(--brand-*)` — satu perubahan tema = satu perubahan seluruh elemen.
5. Verifikasi: tema klasik tetap = sidebar (regression test), shell Premium renderutuh untuk Administrator & Kasir, kasir 403 ke `/jersey/master`.

## E. Belum diterapkan / menunggu keputusan owner

1. **Pengaturan Topbar ala erpv3** (tabel `topbar_tabs`) — bila GF2 juga ingin tab baris kedua bisa diatur per-installation oleh Administrator (label/urutan/ikon/tampil). Butuh: migrasi + model + halaman + gating. Estimasi kecil; prompt owner bila ingin dilanjutkan.
2. **"Full Command Center" (direction erpv3 penuh)** — ice background, kartu 16px, radial wash — bisa dijadikan preset tema ketiga jika owner menyukai.
3. **Font display eksternal** (mis. Fraunces/Lora) — tidak diterapkan: menambah request & FOUT; Georgia serif sudah elegan tanpa beban.
4. Paywall/Lisensi — ditolak (model bisnis berbeda).

## F. Catatan audit erpv3 sendiri yang perlu dicermati bila nanti disamakan

- erpv3 memindahkan navigasi ke topbar tetapi tetap mempertahankan `#mainSidebar` di DOM (tersembunyi via CSS `display:none`) — GF2 justru **tidak merender** aside saat premium (lebih ringan).
- erpv3 menyaring tab modul berdasarkan `Izin::bolehPath()` — GF2 berbuat sama (dipakai `$cekAkses` di kedua tempat).
- `topbar_tabs` erpv3 dibaca setiap request (query kecil, di-cache via model aktif()) — jika diterapkan di GF2, bungkus dengan Cache 5 menit.

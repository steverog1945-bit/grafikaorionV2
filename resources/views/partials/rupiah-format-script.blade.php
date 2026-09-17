{{--
    Partial bersama untuk pemisah ribuan pada input nominal - generalisasi
    dari formatRupiah()/cleanRupiah() yang sebelumnya cuma ada lokal di
    kasir/form_pembayaran.blade.php. Include sekali per halaman, lalu:
    - beri class "format-ribuan" pada <input type="text" inputmode="numeric">
      supaya otomatis tampil "1.000.000" saat diketik.
    - kalau halaman punya JS kalkulasi sendiri yang membaca nilai input ini,
      baca lewat cleanRupiah(el.value) bukan parseFloat(el.value) langsung.
    - saat form displaya name="..." langsung (bukan lewat JS fetch), value
      yang dikirim ke server otomatis dibersihkan dari titik oleh listener
      submit di bawah - controller tetap terima angka polos seperti biasa.

    PENTING - bug yang pernah terjadi & wajib dihindari lagi: formatRupiah()
    HANYA aman dipanggil dengan string HASIL KETIKAN USER (boleh sudah ada
    titik ribuan dari pemanggilan formatRupiah() sebelumnya, mis. "20.000").
    JANGAN PERNAH memanggil formatRupiah(data.sesuatu) langsung dengan nilai
    mentah dari DB/JSON (mis. hasil json_encode($model) atau fetch API) -
    kolom decimal(15,2) di Laravel di-serialize sebagai STRING "20000.00",
    dan formatRupiah() akan salah membaca titik desimal itu sebagai
    pemisah ribuan (dua digit "00" di belakang titik ikut kehitung jadi
    angka signifikan) - "20000.00" bisa berubah jadi "2.000.000" (100x
    lipat). Untuk populate field edit dari data mentah, SELALU pakai
    formatRupiahDariAngka() di bawah, bukan formatRupiah() langsung.
--}}
@once
<script>
    function formatRupiah(angka) {
        let string = angka.toString().replace(/[^0-9]/g, "");
        return string.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Dipakai khusus untuk populate field edit dari nilai mentah DB/JSON
    // (mis. data.harga_promo dari json_encode($model), atau hasil fetch API)
    // - parseFloat dulu supaya ".00" desimal terbaca benar sebagai pecahan
    // (dibuang, bukan disambung jadi digit), baru dibulatkan ke rupiah utuh.
    function formatRupiahDariAngka(nilai) {
        return formatRupiah(Math.round(parseFloat(nilai) || 0));
    }

    function cleanRupiah(string) {
        if (!string) return 0;
        return parseFloat(string.toString().replace(/\./g, '')) || 0;
    }

    document.addEventListener('input', function (e) {
        if (e.target.matches && e.target.matches('input.format-ribuan')) {
            e.target.value = formatRupiah(e.target.value);
        }
    });

    // Jaring pengaman terakhir sebelum form submit biasa (non-JS/fetch) -
    // pastikan nilai yang benar-benar terkirim ke server selalu angka polos,
    // walau ada halaman yang lupa membersihkannya sendiri di kalkulasi JS-nya.
    document.addEventListener('submit', function (e) {
        if (!(e.target instanceof HTMLFormElement)) return;
        e.target.querySelectorAll('input.format-ribuan').forEach(function (input) {
            input.value = cleanRupiah(input.value);
        });
    });
</script>
@endonce

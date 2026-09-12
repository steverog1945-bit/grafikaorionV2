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
--}}
@once
<script>
    function formatRupiah(angka) {
        let string = angka.toString().replace(/[^0-9]/g, "");
        return string.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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

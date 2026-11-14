{{-- Nama barang terstruktur untuk form Pesanan Jersey (create + edit-lines).
    Pola sama dengan POS (penjualan/create.blade.php): deskripsi dibangun dari
    pilihan yang SUDAH dipilih operator (tipe + bahan + lengan/kerah + rincian
    qty ukuran / jumlah pemain), bukan dari kebiasaan mengetik. Begitu operator
    menyunting sendiri kolom nama, pengisian otomatis berhenti (dataset.auto)
    sampai kartu baru dibuat - nama historis item lama tidak pernah ditimpa. --}}
<script>
    (function () {
        'use strict';

        var timerNamaJersey = null;

        function teksPilihan(card, selektor) {
            var el = card.querySelector(selektor);
            if (!el || !el.selectedOptions || !el.selectedOptions[0]) return '';
            var t = el.selectedOptions[0].textContent.trim();
            return (t && t.indexOf('--') !== 0) ? t : '';
        }

        function bangunNamaBarangJersey(card) {
            var bagian = [];
            var tipe = teksPilihan(card, 'select[name$="[jersey_product_type_id]"]');
            if (tipe) bagian.push(tipe);
            var bahan = teksPilihan(card, 'select[name$="[jersey_material_id]"]');
            if (bahan) bagian.push(bahan);
            var lengan = teksPilihan(card, 'select[name$="[jersey_sleeve_type_id]"]');
            if (lengan) bagian.push(lengan);
            var kerah = teksPilihan(card, 'select[name$="[jersey_collar_type_id]"]');
            if (kerah) bagian.push(kerah);

            var modeEl = card.querySelector('input[name$="[order_type]"]:checked');
            var mode = modeEl ? modeEl.value : '';
            var rincian = [];

            if (mode !== 'player_list') {
                card.querySelectorAll('input.input-qty-ukuran').forEach(function (inp) {
                    var q = parseInt(inp.value, 10) || 0;
                    if (q > 0) {
                        var label = inp.previousElementSibling;
                        var namaUkuran = '';
                        if (label && label.firstChild && label.firstChild.nodeType === 3) {
                            namaUkuran = label.firstChild.textContent.trim();
                        }
                        rincian.push(namaUkuran + 'x' + q);
                    }
                });
            }
            if (mode !== 'quantity_only') {
                var jumlahPemain = card.querySelectorAll('input[name$="[player_name]"]').length;
                if (jumlahPemain > 0) rincian.push(jumlahPemain + ' pemain');
            }
            if (rincian.length) bagian.push(rincian.join(', '));

            var hasil = bagian.join(' - ');
            return hasil.length >= 3 ? hasil : '';
        }

        function isiNamaBarangOtomatisJersey(card) {
            var inp = card.querySelector('input[name$="[nama_barang]"]');
            if (!inp || !inp.dataset.auto) return; // kasir menyunting sendiri - jangan ditimpa
            var hasil = bangunNamaBarangJersey(card);
            if (hasil) {
                inp.value = hasil;
                inp.dataset.auto = '1';
            }
        }

        function segarkanNamaJersey(card) {
            clearTimeout(timerNamaJersey);
            timerNamaJersey = setTimeout(function () { isiNamaBarangOtomatisJersey(card); }, 300);
        }

        document.addEventListener('change', function (e) {
            var card = e.target.closest('.jersey-line-card');
            if (!card) return;
            if (e.target.matches('select, input[type="radio"]')) isiNamaBarangOtomatisJersey(card);
        });
        document.addEventListener('input', function (e) {
            var card = e.target.closest('.jersey-line-card');
            if (!card) return;
            if (e.target.matches('input[name$="[nama_barang]"]')) {
                delete e.target.dataset.auto; // ketikan manual = milik operator
                return;
            }
            if (e.target.matches('.input-qty-ukuran, input[name$="[player_name]"]')) {
                segarkanNamaJersey(card);
            }
        });

        // Kartu baris baru boleh diisi otomatis; kartu lama (render server,
        // nama historis) TIDAK diberi flag sehingga tak pernah ditimpa.
        window.tandaiNamaBarangJerseyBaru = function (card) {
            var inp = card.querySelector('input[name$="[nama_barang]"]');
            if (inp) inp.dataset.auto = '1';
        };
    })();
</script>

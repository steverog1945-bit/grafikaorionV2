{{--
    Render QR pembayaran untuk satu atau banyak item SPK sekaligus.
    Variabel wajib: $details (koleksi baris transaksi_detail), $format.
    Satu <script> saja untuk seluruh dokumen supaya cetak gabungan tidak
    memuat ulang library per halaman.
--}}
<script src="{{ asset('vendor/qrcode-generator/qrcode.min.js') }}"></script>
<script>
    (function () {
        @foreach($details as $d)
        (function () {
            const el = document.getElementById('qr-spk-{{ $d->id_detail }}');
            if (!el) return;
            const qr = qrcode(0, 'M');
            qr.addData(@json(\App\Models\AppSetting::urlAkses() . '/kasir/bayar-form/' . $d->id_transaksi));
            qr.make();
            el.innerHTML = qr.createSvgTag({ cellSize: {{ $format === 'thermal' ? 3 : 4 }}, margin: 2, scalable: true });
            const svg = el.querySelector('svg');
            if (svg) {
                svg.style.width = '{{ $format === 'thermal' ? 120 : 150 }}px';
                svg.style.height = '{{ $format === 'thermal' ? 120 : 150 }}px';
            }
        })();
        @endforeach
    })();
</script>

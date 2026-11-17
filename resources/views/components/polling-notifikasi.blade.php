@props([
    'url',           // endpoint yang dipanggil, harus balikan JSON {"total": N}
    'jumlahAwal',    // jumlah saat halaman pertama dimuat, pembanding polling
    'pesan' => 'Ada data baru.',
    'interval' => 10000, // ms
])

{{--
    Komponen polling generik - dipakai di halaman lintas divisi (SPK,
    Produksi, Collecting) supaya operator tahu ada pekerjaan/data baru
    dari divisi lain TANPA harus manual refresh (F5) berkali-kali.

    SENGAJA polling berkala (bukan WebSocket/real-time push) - app ini
    server-rendered Blade biasa tanpa infrastruktur broadcasting
    (Reverb/Pusher/dsb), jadi polling adalah cara paling sederhana &
    murah untuk kasus ini. Cuma MENAMPILKAN notifikasi "ada data baru,
    klik untuk lihat" - tidak auto-reload data di baliknya (operator
    tetap yang memutuskan kapan refresh, supaya tidak kehilangan draft
    form yang sedang diisi kalau notifikasi muncul di tengah kerja).
--}}
<div id="polling-notif-{{ md5($url) }}" class="hidden fixed top-20 right-6 z-50 animate-bounce">
    <button onclick="window.location.reload()"
        class="flex items-center gap-2 bg-indigo-600 text-white text-[12px] font-medium px-4 py-3 rounded-2xl shadow-sm hover:bg-indigo-700 transition-all">
        <i class="fas fa-bell"></i>
        {{ $pesan }}
        <i class="fas fa-rotate-right ml-1"></i>
    </button>
</div>

<script>
(function () {
    let jumlahAwal = {{ (int) $jumlahAwal }};
    const el = document.getElementById('polling-notif-{{ md5($url) }}');
    let gagalBeruntun = 0;

    function cekDataBaru() {
        fetch('{{ $url }}')
            .then(res => res.ok ? res.json() : Promise.reject())
            .then(data => {
                gagalBeruntun = 0;
                if (data.total > jumlahAwal) {
                    el.classList.remove('hidden');
                }
            })
            .catch((err) => {
                gagalBeruntun += 1;
                // Sebelumnya: gagal sepenuhnya tanpa jejak - operator mengira
                // "tidak ada pekerjaan baru" padahal pollingnya yang mati
                // (mis. sesi habis). Sekarang minimal tercatat di console +
                // tombol lonceng redup setelah 3 kegagalan berturut-turut
                // diberi tanda lewat title (bukan mengubah layout).
                if (console && console.warn) console.warn('[polling-notifikasi] gagal (' + gagalBeruntun + 'x) \u2014 cek sesi/masuk ulang');
                if (gagalBeruntun >= 3) el.setAttribute('title', 'Polling gagal ' + gagalBeruntun + 'x — muat ulang halaman (F5) atau masuk ulang');
            });
    }

    setInterval(cekDataBaru, {{ (int) $interval }});
})();
</script>

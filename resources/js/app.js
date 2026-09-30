import './bootstrap';
import 'bootstrap'; // Bootstrap JS

/* SweetAlert2 SENGAJA tidak di-import di sini.
   Layout memuatnya sebagai skrip klasik di <head>
   (public/vendor/sweetalert2), karena ada skrip inline di badgean
   document yang memanggil Swal.mixin() dan Swal.fire() saat halaman
   sedang di-parse. Script type="module" selalu deferred, jadi import
   di sini baru tersedia setelah badgean selesai - terlambat untuk
   pemanggilan itu, dan hasilnya "Swal is not defined".

   Module ini tidak perlu menaruhnya di window juga: layout sudah
   memuat pustakanya duluan, jadi window.Swal sudah ada. */

/* Tooltip & Popover Bootstrap */
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

window.deleteConfirm = function (formId) {
    Swal.fire({
        title: 'Hapus Bahan?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#f1f5f9',
        cancelButtonText: '<span style="color:#64748b">Batal</span>',
        confirmButtonText: 'Ya, Hapus!',
        customClass: { popup: 'rounded-lg', confirmButton: 'rounded-md px-4 py-2', cancelButton: 'rounded-md px-4 py-2' }
    }).then((result) => {
        if (result.isConfirmed) document.getElementById(formId).submit();
    });
};

/* ==========================================================================
   API MODAL
   ==========================================================================
   Satu-satunya cara membuka/menutup modal di seluruh aplikasi ini.

   Yang ditangani di sini (sebelumnya hanya ganti class, sehingga
   tombol tutup, Esc, dan klik luar tidak bekerja di banyak halaman):
     1. buka / tutup dengan animasi (scale + opacity, 200ms ease-out)
     2. tutup saat Esc ditekan
     3. tutup saat area gelap di luar panel diklik
     4. tombol di dalam panel bertanda data-modal-close
     5. kembalikan fokus ke elemen pemicu setelah ditutup
     6. kunci scroll halaman selama modal terbuka, dan buka kembali
        dengan benar bila ada modal lain masih terbuka

   Root modal memakai atribut `hidden` (bukan kelas), jadi elemen yang
   tertutup tidak bisa difokus atau dibaca screen reader.
   ========================================================================== */

const MODAL_TRANSITION_MS = 200;
let modalOpenCount = 0;
let lastFocused = null;

function modalPanel(el) {
    return el.querySelector('[data-modal-panel]') || el.firstElementChild;
}

function lockScroll() {
    modalOpenCount += 1;
    if (modalOpenCount !== 1) return;
    document.body.classList.add('overflow-hidden');
    // Hapus gaya inline yang sisa dari halaman sebelumnya. Beberapa
    // view lama menulis body.style.overflow = 'hidden' sendiri saat
    // membuka modal, lalu menutupnya lewat API ini - sehingga nilai
    // inline itu tertinggal dan halaman tidak bisa digulir lagi.
    document.body.style.overflow = '';
}

function unlockScroll() {
    modalOpenCount = Math.max(0, modalOpenCount - 1);
    if (modalOpenCount !== 0) return;
    document.body.classList.remove('overflow-hidden');
    // WAJIB dibersihkan, bukan cuma kelas Tailwind. Kalau view mana pun
    // pernah menyetel body.style.overflow = 'hidden' langsung, nilainya
    // menang atas kelas dan tidak hilang bersama kelasnya. Akibatnya
    // seluruh halaman jadi tidak bisa digulir setelah modal ditutup, dan
    // satu-satunya jalan keluar adalah muat ulang - persis gejala "stuck"
    // yang dilaporkan. Membersihkannya di sini menutup celah itu untuk
    // semua halaman, Sekalipun ada view yang belum ikut diperbaiki.
    document.body.style.overflow = '';
}

/* Ada DUA cara menyembunyikan modal di aplikasi ini, dan keduanya
   masih dipakai sekalian:
     - <x-modal> menyembunyikannya lewat ATRIBUT `hidden`
     - modal yang ditulis manual per halaman lewat KELAS `hidden`
   Kalau hanya salah satu yang dikenali, modal manual terbaca "sudah
   terbuka" padahal tidak terlihat: tombol tidak melakukan apa-apa, dan
   Escape tidak menutup apa pun. Jadi keduanya selalu ditangani
   bersamaan di bawah. */
function isTertutup(el) {
    return el.hasAttribute('hidden') || el.classList.contains('hidden');
}

function tampilkan(el) {
    el.removeAttribute('hidden');
    el.classList.remove('hidden');
    // Hanya modal dari komponen yang butuh `flex`; modal manual sudah
    // menata dirinya lewat anak di dalamnya (min-h-screen + flex).
    if (el.querySelector(':scope > [data-modal-panel]')) el.classList.add('flex');
}

function sembunyikan(el) {
    el.setAttribute('hidden', '');
    el.classList.add('hidden');
    el.classList.remove('flex');
}

/* Semua modal yang sedang terbuka, untuk mendeteksi Escape.
   Selector memakai data-modal-root, BUKAN [id^="modal-"]. Id yang
   diawali "modal-" juga dipakai judul di dalam modal
   (h3 id="modal-{id}-title"), dan dulu selector prefix ikut
   menangkapnya: Escape menutup judulnya, bukan panelnya, sehingga teks
   judul hilang tapi modal tetap menggantung di layar. */
function openModals() {
    return Array.from(document.querySelectorAll('[data-modal-root]')).filter((m) => !isTertutup(m));
}

function focusFirstIn(panel) {
    const target = panel.querySelector(
        'input:not([type=hidden]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [data-autofocus]'
    );
    if (target) {
        // Cegah browser menggulir ke bawah karena fokus.
        target.focus({ preventScroll: true });
    } else {
        panel.focus({ preventScroll: true });
    }
}

window.openModal = function (id) {
    const el = document.getElementById('modal-' + id) || document.getElementById(id);
    if (!el || !isTertutup(el)) return;

    const panel = modalPanel(el);
    lastFocused = document.activeElement;

    tampilkan(el);
    el.dataset.state = 'opening';
    lockScroll();

    // Pindah ke state "open" setelah browser selesai merender frame
    // pertama, supaya transisi masuk benar-benar terlihat. rAF
    // ditunda/dilewati ketika tab tidak terlihat, jadi ada jaring
    // pengaman berbasis waktu: tanpa itu modal bisa tertahan di
    // opacity 0 (tidak terlihat) padahal statusnya sudah terbuka.
    const bukaPenuh = () => { if (el.dataset.state === 'opening') el.dataset.state = 'open'; };
    requestAnimationFrame(bukaPenuh);
    setTimeout(bukaPenuh, 32);

    // Fokus setelah frame pertama supaya animasi masuk selesai dimulai.
    if (panel) setTimeout(() => focusFirstIn(panel), 60);

    document.addEventListener('keydown', onModalKeydown, true);
    return false;
};

window.closeModal = function (id) {
    const el = id ? (document.getElementById('modal-' + id) || document.getElementById(id)) : openModals().pop();
    if (!el || isTertutup(el)) return;

    el.dataset.state = 'closing';

    const done = () => {
        sembunyikan(el);
        el.dataset.state = 'closed';
        el.removeAttribute('data-state');
        unlockScroll();

        if (lastFocused && document.contains(lastFocused)) {
            lastFocused.focus({ preventScroll: true });
        }
        lastFocused = null;
    };

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced) done();
    else setTimeout(done, MODAL_TRANSITION_MS);

    if (!openModals().length) {
        document.removeEventListener('keydown', onModalKeydown, true);
    }
    return false;
};

/* Alias untuk halaman lama yang memakai toggleModal(namaTanpaPrefix). */
window.toggleModal = function (id) {
    const el = document.getElementById('modal-' + id) || document.getElementById(id);
    if (!el) return;
    if (isTertutup(el)) window.openModal(id);
    else window.closeModal(id);
};

function onModalKeydown(e) {
    if (e.key !== 'Escape') return;
    const open = openModals();
    if (!open.length) return;
    e.stopPropagation();
    window.closeModal(open[open.length - 1].id);
}

/* Klik di luar panel, dan tombol bertanda data-modal-close.
   Dipasang sekali di document, jadi berlaku untuk modal yang baru
   dibuat halaman mana pun tanpa perlu registering ulang. */
document.addEventListener('click', function (e) {
    const closer = e.target.closest('[data-modal-close]');
    if (closer) {
        const root = closer.closest('[data-modal-root]');
        if (root) {
            e.preventDefault();
            window.closeModal(root.id);
        }
        return;
    }

    // Area gelap = elemen yang menjadi anak langsung root modal
    // dan ditandai data-modal-backdrop.
    if (e.target.matches('[data-modal-backdrop]')) {
        const root = e.target.closest('[data-modal-root]');
        if (root) {
            e.preventDefault();
            window.closeModal(root.id);
        }
    }
}, true);

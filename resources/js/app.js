import './bootstrap';
import 'bootstrap'; // Import JS Bootstrap
import Swal from 'sweetalert2';

// Daftarkan SweetAlert ke window agar bisa dipanggil langsung di file Blade
window.Swal = Swal; 
console.log('ERP-Cetak Aset: Ready!');
// Inisialisasi Tooltip & Popover Bootstrap (Opsional tapi berguna untuk ERP)
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
window.deleteConfirm = function(formId) {
    Swal.fire({
        title: 'Hapus Bahan?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5', // Indigo-600
        cancelButtonColor: '#f1f5f9',
        cancelButtonText: '<span style="color: #64748b">Batal</span>',
        confirmButtonText: 'Ya, Hapus!',
        customClass: {
            popup: 'rounded-3xl',
            confirmButton: 'rounded-xl px-4 py-2',
            cancelButton: 'rounded-xl px-4 py-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    })
}

/**
 * API modal terpusat untuk component <x-modal id="...">.
 * Sebelumnya tiap halaman punya API sendiri untuk hal yang sama
 * (openTambahModal()/closeModal() di halaman bahan, toggleModal() di
 * halaman supplier). Ke depan, semua modal baru cukup pakai 2 fungsi ini.
 * Root element <x-modal> dirender dengan id="modal-{id}".
 */
window.openModal = function (id) {
    const el = document.getElementById('modal-' + id);
    if (!el) return;
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
};

window.closeModal = function (id) {
    const el = document.getElementById('modal-' + id);
    if (!el) return;
    el.classList.add('hidden');
    el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
};
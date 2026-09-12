{{-- Baris tabel entitas sederhana (desainer/tipe produk/kategori) - HARUS
     hasilkan markup yang sama persis dengan baseRowHtml() di JS pada
     master/index.blade.php, karena baris hasil AJAX add memakai fungsi JS
     itu sedangkan render awal halaman memakai partial ini. --}}
<tr data-id="{{ $row->id }}">
    <td class="p-3 font-semibold">{{ $row->name }}</td>
    <td class="p-3 text-center" data-status>
        @if($row->is_active)
            <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase bg-slate-100 text-slate-700">Aktif</span>
        @else
            <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase bg-slate-50 text-slate-300 border border-slate-100">Nonaktif</span>
        @endif
    </td>
    <td class="p-3 text-center">
        <div class="flex items-center justify-center gap-1">
            <button type="button" onclick="bukaEditSimple('{{ $entity }}', {{ $row->id }}, {{ Illuminate\Support\Js::from($row->name) }})" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100" title="Ubah"><i class="fas fa-pen text-xs"></i></button>
            <button type="button" onclick="toggleEntitas('{{ $entity }}', {{ $row->id }})" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100" title="Aktif/Nonaktif"><i class="fas fa-power-off text-xs"></i></button>
        </div>
    </td>
</tr>

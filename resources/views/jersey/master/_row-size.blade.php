{{-- Sinkron dengan baseRowSizeHtml() di JS master/index.blade.php. --}}
<tr data-id="{{ $row->id }}" class="{{ $row->is_active ? '' : 'opacity-50' }}">
    <td class="p-2.5 font-semibold">{{ $row->name }}</td>
    <td class="p-2.5 text-center">{{ $row->sort_order }}</td>
    <td class="p-2.5 text-right">Rp {{ number_format($row->price_surcharge, 0, ',', '.') }}</td>
    <td class="p-2.5 text-center">
        <div class="flex items-center justify-center gap-1">
            <button type="button" onclick="bukaEditUkuran({{ Illuminate\Support\Js::from(["id" => $row->id, "name" => $row->name, "sort_order" => $row->sort_order, "price_surcharge" => $row->price_surcharge]) }})" class="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50" title="Ubah"><i class="far fa-edit text-xs"></i></button>
            <button type="button" onclick="toggleUkuran({{ $row->id }})" class="w-8 h-8 rounded-lg text-slate-400 {{ $row->is_active ? 'hover:text-slate-600 hover:bg-slate-100' : 'hover:text-emerald-500 hover:bg-emerald-50' }}" title="{{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"><i class="far {{ $row->is_active ? 'fa-eye-slash' : 'fa-eye' }} text-xs"></i></button>
        </div>
    </td>
</tr>

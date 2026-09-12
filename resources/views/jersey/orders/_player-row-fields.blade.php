{{-- Satu baris "Daftar Pemain" - dipakai di template JS (item=null, __ITEM__
     placeholder) dan edit-lines.blade.php (item diisi data pemain lama). --}}
<tr class="baris-pemain">
    <td class="p-1"><input type="text" name="{{ $prefix }}[items][{{ $itemIdx }}][player_name]" value="{{ $item->player_name ?? '' }}" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
    <td class="p-1"><input type="text" name="{{ $prefix }}[items][{{ $itemIdx }}][player_number]" value="{{ $item->player_number ?? '' }}" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
    <td class="p-1">
        <select name="{{ $prefix }}[items][{{ $itemIdx }}][size]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
            @foreach($sizes as $s)<option value="{{ $s->name }}" @selected(($item->size ?? null) === $s->name)>{{ $s->name }}</option>@endforeach
        </select>
    </td>
    <td class="p-1">
        <select name="{{ $prefix }}[items][{{ $itemIdx }}][gender]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
            <option value="L" @selected(($item->gender ?? 'L') === 'L')>L</option>
            <option value="P" @selected(($item->gender ?? null) === 'P')>P</option>
        </select>
    </td>
    <td class="p-1">
        <select name="{{ $prefix }}[items][{{ $itemIdx }}][sleeve]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
            <option value="Pendek" @selected(($item->sleeve ?? 'Pendek') === 'Pendek')>Pendek</option>
            <option value="Panjang" @selected(($item->sleeve ?? null) === 'Panjang')>Panjang</option>
        </select>
    </td>
    <td class="p-1"><input type="text" name="{{ $prefix }}[items][{{ $itemIdx }}][collar]" value="{{ $item->collar ?? '' }}" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
    <td class="p-1 text-center"><button type="button" onclick="this.closest('tr').remove()" class="w-6 h-6 text-rose-400"><i class="far fa-trash-alt text-[10px]"></i></button></td>
</tr>

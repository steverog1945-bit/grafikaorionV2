{{-- Satu baris "Daftar Pemain" - dipakai di template JS (item=null, __ITEM__
     placeholder) dan edit-lines.blade.php (item diisi data pemain lama). --}}
<tr class="baris-pemain">
    <td class="p-1"><input type="text" name="{{ $prefix }}[items][{{ $itemIdx }}][player_name]" value="{{ $item->player_name ?? '' }}" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
    <td class="p-1"><input type="text" name="{{ $prefix }}[items][{{ $itemIdx }}][player_number]" value="{{ $item->player_number ?? '' }}" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
    <td class="p-1">
        <select name="{{ $prefix }}[items][{{ $itemIdx }}][size]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
            @foreach($sizes->groupBy(fn ($sz) => ($sz->kelompok ?? 'dewasa') === 'anak' ? 'anak' : 'dewasa') as $kel => $grupSize)
            <optgroup label="{{ $kel === 'anak' ? 'Anak' : 'Dewasa' }}">
                @foreach($grupSize as $s)<option value="{{ $s->name }}" @selected(($item->size ?? null) === $s->name)>{{ $s->name }}</option>@endforeach
            </optgroup>
            @endforeach
        </select>
    </td>
    <td class="p-1">
        <select name="{{ $prefix }}[items][{{ $itemIdx }}][gender]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
            <option value="L" @selected(($item->gender ?? 'L') === 'L')>L</option>
            <option value="P" @selected(($item->gender ?? null) === 'P')>P</option>
        </select>
    </td>
    <td class="p-1 col-lengan-kerah">
        <select name="{{ $prefix }}[items][{{ $itemIdx }}][jersey_sleeve_type_id]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
            <option value="">-- Lengan --</option>
            @foreach($sleeveTypes as $st)<option value="{{ $st->id }}" @selected(($item->jersey_sleeve_type_id ?? null) == $st->id)>{{ $st->name }}</option>@endforeach
        </select>
    </td>
    <td class="p-1 col-lengan-kerah">
        <select name="{{ $prefix }}[items][{{ $itemIdx }}][jersey_collar_type_id]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
            <option value="">-- Kerah --</option>
            @foreach($collarTypes as $ct)<option value="{{ $ct->id }}" @selected(($item->jersey_collar_type_id ?? null) == $ct->id)>{{ $ct->name }}</option>@endforeach
        </select>
    </td>
    <td class="p-1 text-center"><button type="button" onclick="this.closest('tr').remove(); if (typeof jadwalkanPreviewHarga === 'function') jadwalkanPreviewHarga(); if (typeof jadwalkanPreviewHargaSusulan === 'function') jadwalkanPreviewHargaSusulan();" class="w-6 h-6 text-rose-400"><i class="far fa-trash-alt text-[10px]"></i></button></td>
</tr>

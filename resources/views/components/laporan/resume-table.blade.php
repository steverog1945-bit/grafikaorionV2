@props([
    'title' => 'Resume',
    'rows'  => [], // [label => value string, ...] - value sudah diformat pemanggil
])

{{--
    Tabel ringkasan standar untuk laporan - dipakai SAMA persis di layar
    maupun saat dicetak (tidak ada lagi versi kartu berwarna di layar +
    versi polos khusus print) supaya konsisten dan paddingnya tidak
    berlebihan seperti kartu dashboard. Border tipis, padding rapat,
    tanpa rounded/shadow - meniru gaya tabel laporan cetak standar.
--}}
<div class="mb-6 border border-slate-200 rounded-md overflow-hidden">
    <p class="text-[11px] font-bold text-slate-700 uppercase tracking-wide px-3 py-2 bg-slate-50 border-b border-slate-200">{{ $title }}</p>
    <table class="w-full text-[12.5px]">
        <tbody class="divide-y divide-slate-100">
            @foreach($rows as $label => $value)
            <tr>
                <td class="px-3 py-1.5 text-slate-500 w-1/2 sm:w-2/5">{{ $label }}</td>
                <td class="px-3 py-1.5 font-semibold text-slate-800 text-right sm:text-left">{!! $value !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

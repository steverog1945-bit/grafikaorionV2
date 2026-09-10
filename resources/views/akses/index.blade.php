@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Pengaturan Hak Akses" subtitle="Tentukan menu apa saja yang boleh dibuka oleh setiap Role." />

<form action="{{ route('akses.update') }}" method="POST">
    @csrf
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white">
                        <th class="p-5 text-sm font-semibold uppercase tracking-wider border-r border-slate-800">Menu / Fitur</th>
                        @foreach($roles as $role)
                            <th class="p-5 text-sm font-semibold uppercase tracking-wider text-center border-r border-slate-800 last:border-0">
                                {{ $role->nama_role }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($menus as $menu)
                    <tr class="hover:bg-indigo-50/50 transition-colors group">
                        <td class="p-5 border-r border-slate-100">
                            <div class="flex items-center">
                                <span class="text-lg mr-3 group-hover:scale-120 transition-transform">📂</span>
                                <span class="font-bold text-slate-700">{{ $menu->nama_menu }}</span>
                            </div>
                        </td>
                        
                        @foreach($roles as $role)
                        <td class="p-5 text-center border-r border-slate-100 last:border-0">
                            @php
                                $cek = DB::table('role_permissions')
                                        ->where('id_role', $role->id_role)
                                        ->where('id_menu', $menu->id_menu)
                                        ->exists();
                            @endphp
                            
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="permissions[{{ $role->id_role }}][]" 
                                       value="{{ $menu->id_menu }}"
                                       {{ $cek ? 'checked' : '' }}
                                       class="w-6 h-6 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-2 transition-all cursor-pointer">
                            </label>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 flex justify-end">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-bold shadow-xl shadow-indigo-100 transition-all flex items-center gap-2 group active:scale-95">
            <span>💾</span> Simpan Seluruh Perubahan Akses
        </button>
    </div>
</form>

</div>
@endsection
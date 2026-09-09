@extends('layouts.app')

@section('content')
<div class="mb-8 border-b border-slate-200 pb-4">
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard</h1>
    <p class="text-slate-500">Selamat datang kembali di sistem kendali produksi.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="bg-indigo-100 p-3 rounded-2xl text-2xl">👤</div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">User Aktif</p>
                <h3 class="text-xl font-bold text-slate-900">{{ Auth::user()->username }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="bg-emerald-100 p-3 rounded-2xl text-2xl">🛡️</div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Role Anda</p>
                <h3 class="text-xl font-bold text-slate-900">{{ Auth::user()->role->nama_role }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="bg-indigo-900 text-white p-6 rounded-3xl shadow-xl shadow-indigo-100 relative overflow-hidden">
    <div class="relative z-10">
        <h4 class="text-lg font-bold mb-2 flex items-center gap-2">
            <span>🚀</span> Sistem Percetakan Kompleks
        </h4>
        <p class="text-indigo-100 leading-relaxed">
            Menu di sebelah kiri muncul berdasarkan izin (*permissions*) yang diberikan pada database untuk Role 
            <span class="bg-indigo-700 px-3 py-1 rounded-lg font-bold text-white mx-1 border border-indigo-500">
                {{ Auth::user()->role->nama_role }}
            </span>.
        </p>
    </div>
    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-800 rounded-full opacity-50"></div>
</div>
@endsection
@php($appSetting = \App\Models\AppSetting::current())
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $appSetting->nama_aplikasi }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            @if($appSetting->logo_path)
                <img src="{{ asset('storage/' . $appSetting->logo_path) }}" alt="Logo" class="w-16 h-16 mx-auto mb-3 object-contain">
            @endif
            <h1 class="text-3xl font-extrabold text-indigo-600 tracking-tighter">{{ $appSetting->nama_aplikasi }}</h1>
            <p class="text-slate-500 mt-2 font-medium">Sistem Kendali Produksi Terpadu</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
            <div class="p-8 md:p-10">
                <h2 class="text-xl font-bold text-slate-800 mb-6">Selamat Datang Kembali</h2>
                
                <form action="{{ url('login') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Username</label>
                        <input type="text" name="username" 
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none" 
                            placeholder="Masukkan username anda" required autofocus>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Password</label>
                        <input type="password" name="password" 
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none" 
                            placeholder="••••••••" required>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl text-sm flex items-center gap-2">
                            <span>⚠️</span> {{ $errors->first() }}
                        </div>
                    @endif

                    <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-[0.98]">
                        Masuk ke Sistem
                    </button>
                </form>
            </div>
            
            <div class="bg-slate-50 p-6 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400 font-medium italic">
                    Gunakan akun yang telah didaftarkan oleh Administrator.
                </p>
            </div>
        </div>
    </div>

</body>
</html>
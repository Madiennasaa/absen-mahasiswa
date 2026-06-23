<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi RFID</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #090d16;
            color: #f1f5f9;
        }
        .glass-login {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Ambient glow effects -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl"></div>

    <div class="w-full max-w-md glass-login p-8 rounded-3xl shadow-2xl relative z-10">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14">
                <img src="{{ asset('polinema.png') }}" alt="Logo Polinema" class="w-full h-full object-contain drop-shadow-md">
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Selamat Datang Kembali</h1>
            <p class="text-sm text-slate-400 mt-1">Silakan masuk ke akun Anda</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            
            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Alamat Email</label>
                <div class="relative">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                           class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all placeholder:text-slate-600"
                           placeholder="nama@email.com">
                </div>
                @error('email')
                    <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Kata Sandi</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all placeholder:text-slate-600"
                           placeholder="••••••••">
                </div>
                @error('password')
                    <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-800 text-indigo-500 focus:ring-0 focus:ring-offset-0">
                    <span class="text-xs text-slate-400">Ingat saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-medium rounded-xl text-sm transition-all shadow-lg shadow-indigo-500/10 active:scale-[0.98]">
                Masuk
            </button>
        </form>

        <div class="mt-8 text-center border-t border-slate-800/60 pt-4">
            <p class="text-xs text-slate-500">Sistem Absensi Mahasiswa berbasis IoT</p>
        </div>
    </div>

</body>
</html>

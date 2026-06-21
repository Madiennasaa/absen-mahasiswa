@extends('layouts.app')

@section('title', 'Tambah Dosen')
@section('page_title', 'Tambah Akun Dosen Baru')

@section('content')
<div class="max-w-xl mx-auto glass-panel p-8 rounded-2xl shadow-xl">
    <div class="mb-6">
        <a href="{{ route('admin.dosen.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Dosen
        </a>
    </div>

    <form action="{{ route('admin.dosen.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama lengkap dosen"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            @error('name')
                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Alamat Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: dosen@email.com"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            @error('email')
                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kata Sandi</label>
            <input type="password" id="password" name="password" required placeholder="Minimal 6 karakter"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            @error('password')
                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-medium rounded-xl text-sm transition-all shadow-lg shadow-indigo-500/10 active:scale-[0.98]">
            Simpan Akun Dosen
        </button>
    </form>
</div>
@endsection

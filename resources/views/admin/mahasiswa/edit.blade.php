@extends('layouts.app')

@section('title', 'Edit Mahasiswa')
@section('page_title', 'Edit Data Mahasiswa')

@section('content')
<div class="max-w-xl mx-auto glass-panel p-8 rounded-2xl shadow-xl">
    <div class="mb-6">
        <a href="{{ route('admin.mahasiswa.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Mahasiswa
        </a>
    </div>

    <form action="{{ route('admin.mahasiswa.update', $mahasiswa->uid_ktm) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <!-- UID KTM -->
        <div>
            <label for="uid_ktm" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">UID KTM (HEX)</label>
            <input type="text" id="uid_ktm" name="uid_ktm" value="{{ old('uid_ktm', $mahasiswa->uid_ktm) }}" required placeholder="Contoh: A1B2C3D4"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all font-mono">
            @error('uid_ktm')
                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- NIM -->
        <div>
            <label for="nim" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">NIM</label>
            <input type="text" id="nim" name="nim" value="{{ old('nim', $mahasiswa->nim) }}" required placeholder="Masukkan NIM mahasiswa"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            @error('nim')
                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Nama -->
        <div>
            <label for="nama" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $mahasiswa->nama) }}" required placeholder="Masukkan nama lengkap"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            @error('nama')
                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Kelas -->
        <div>
            <label for="kelas" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kelas</label>
            <input type="text" id="kelas" name="kelas" value="{{ old('kelas', $mahasiswa->kelas) }}" required placeholder="Contoh: 3A"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            @error('kelas')
                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Program Studi -->
        <div>
            <label for="prodi" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Program Studi</label>
            <input type="text" id="prodi" name="prodi" value="{{ old('prodi', $mahasiswa->prodi) }}" required placeholder="Contoh: Teknik Informatika"
                   class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            @error('prodi')
                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-medium rounded-xl text-sm transition-all shadow-lg shadow-indigo-500/10 active:scale-[0.98]">
            Perbarui Data
        </button>
    </form>
</div>
@endsection

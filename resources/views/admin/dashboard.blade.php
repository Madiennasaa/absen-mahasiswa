@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard Admin')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Mahasiswa -->
    <div class="glass-panel p-6 rounded-2xl flex items-center justify-between shadow-xl">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Total Mahasiswa</span>
            <span class="text-3xl font-bold text-white">{{ $totalMahasiswa }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
    </div>

    <!-- Total Dosen -->
    <div class="glass-panel p-6 rounded-2xl flex items-center justify-between shadow-xl">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Total Dosen</span>
            <span class="text-3xl font-bold text-white">{{ $totalDosen }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
        </div>
    </div>

    <!-- Absen Hari Ini -->
    <div class="glass-panel p-6 rounded-2xl flex items-center justify-between shadow-xl">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Absen Hari Ini</span>
            <span class="text-3xl font-bold text-emerald-400">{{ $absenHariIni }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    <!-- Terlambat Hari Ini -->
    <div class="glass-panel p-6 rounded-2xl flex items-center justify-between shadow-xl">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Terlambat Hari Ini</span>
            <span class="text-3xl font-bold text-amber-400">{{ $terlambatHariIni }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Recent Activity Table -->
    <div class="lg:col-span-2 glass-panel p-6 rounded-2xl shadow-xl">
        <h3 class="text-lg font-semibold text-white mb-4">Absensi Terbaru</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-xs font-semibold text-slate-400 uppercase">
                        <th class="py-3 px-4">Nama</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-sm">
                    @forelse($recentAbsensi as $absen)
                        <tr>
                            <td class="py-3.5 px-4 font-medium text-white">{{ $absen->mahasiswa->nama ?? 'Unknown' }}</td>
                            <td class="py-3.5 px-4 text-slate-300">{{ $absen->mahasiswa->kelas ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $absen->waktu_masuk->format('H:i:s') }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $absen->status === 'Hadir' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                                    {{ $absen->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">Belum ada absensi hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions / Setup Panel -->
    <div class="glass-panel p-6 rounded-2xl shadow-xl flex flex-col justify-between">
        <div>
            <h3 class="text-lg font-semibold text-white mb-4">Akses Cepat</h3>
            <p class="text-sm text-slate-400 mb-6">Kelola data mahasiswa atau daftarkan akun dosen baru dengan cepat.</p>
            
            <div class="space-y-3">
                <a href="{{ route('admin.mahasiswa.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 text-sm font-medium text-slate-200 hover:text-white transition-all">
                    <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">+</span>
                    Tambah Mahasiswa Baru
                </a>
                <a href="{{ route('admin.dosen.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 text-sm font-medium text-slate-200 hover:text-white transition-all">
                    <span class="w-8 h-8 rounded-lg bg-violet-500/10 text-violet-400 flex items-center justify-center">+</span>
                    Tambah Akun Dosen
                </a>
            </div>
        </div>

        <div class="mt-6 border-t border-slate-800/60 pt-4 flex items-center justify-between text-xs text-slate-500">
            <span>Sistem Absensi v1.0</span>
            <span>Laravel 13 & MicroPython</span>
        </div>
    </div>
</div>
@endsection

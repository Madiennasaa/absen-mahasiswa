@extends('layouts.app')

@section('title', 'Riwayat Scan RFID')
@section('page_title', 'Riwayat Scan RFID')

@section('content')
<div class="glass-panel p-6 rounded-2xl shadow-xl mb-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <form action="{{ route('admin.riwayat_scan.index') }}" method="GET" class="w-full flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama, NIM, UID..." 
                       class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            </div>
            
            <div>
                <select name="kelas" class="bg-slate-900/60 border border-slate-800 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                    <option value="">Semua Kelas</option>
                    @foreach($allKelas as $k)
                        <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <select name="prodi" class="bg-slate-900/60 border border-slate-800 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                    <option value="">Semua Prodi</option>
                    @foreach($allProdi as $p)
                        <option value="{{ $p }}" {{ request('prodi') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-slate-850 hover:bg-slate-800 text-slate-200 font-medium rounded-xl text-sm transition-all border border-slate-800">
                Filter
            </button>
            
            @if(request()->anyFilled(['search', 'kelas', 'prodi']))
                <a href="{{ route('admin.riwayat_scan.index') }}" class="px-4 py-2.5 text-slate-400 hover:text-slate-200 text-sm transition-all">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800">
                    <th class="py-3 px-4 text-xs font-medium text-slate-400 uppercase tracking-wider">Waktu Scan</th>
                    <th class="py-3 px-4 text-xs font-medium text-slate-400 uppercase tracking-wider">UID Kartu</th>
                    <th class="py-3 px-4 text-xs font-medium text-slate-400 uppercase tracking-wider">Mahasiswa</th>
                    <th class="py-3 px-4 text-xs font-medium text-slate-400 uppercase tracking-wider">Kelas/Prodi</th>
                    <th class="py-3 px-4 text-xs font-medium text-slate-400 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse($riwayatScans as $riwayat)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="text-sm font-medium text-white">{{ $riwayat->created_at->format('H:i:s') }}</div>
                            <div class="text-xs text-slate-500">{{ $riwayat->created_at->translatedFormat('l, d M Y') }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="inline-flex items-center px-2 py-1 rounded text-xs font-mono font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                {{ $riwayat->uid_ktm }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-sm font-medium text-white">{{ $riwayat->nama ?? '-' }}</div>
                            <div class="text-xs text-slate-400">{{ $riwayat->nim ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($riwayat->kelas !== '-' && $riwayat->prodi !== '-')
                                <div class="text-sm text-slate-300">Kelas {{ $riwayat->kelas }}</div>
                                <div class="text-xs text-slate-500">{{ $riwayat->prodi }}</div>
                            @else
                                <span class="text-xs text-slate-500 italic">Belum terdaftar di kelas</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if(str_contains($riwayat->status, 'Hadir'))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $riwayat->status }}
                                </span>
                            @elseif(str_contains($riwayat->status, 'Terlambat'))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ $riwayat->status }}
                                </span>
                            @elseif(str_contains($riwayat->status, 'Ditolak'))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    {{ $riwayat->status }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    {{ $riwayat->status }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 text-sm">
                            Belum ada riwayat scan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 border-t border-slate-800 pt-4">
        {{ $riwayatScans->links() }}
    </div>
</div>
@endsection

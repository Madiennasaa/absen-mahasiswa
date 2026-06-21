@extends('layouts.app')

@section('title', 'Data Dosen')
@section('page_title', 'Kelola Akun Dosen')

@section('content')
<div class="glass-panel p-6 rounded-2xl shadow-xl mb-8">
    <div class="flex items-center justify-between gap-4 mb-6">
        <h3 class="text-lg font-semibold text-white">Daftar Dosen</h3>
        
        <a href="{{ route('admin.dosen.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-medium rounded-xl text-sm transition-all shadow-lg shadow-indigo-500/10">
            + Tambah Dosen
        </a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800 text-xs font-semibold text-slate-400 uppercase">
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Kelas</th>
                    <th class="py-3 px-4">Prodi</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40 text-sm">
                @forelse($dosen as $dsn)
                    <tr class="hover:bg-slate-900/10 transition-colors">
                        <td class="py-3.5 px-4 font-medium text-white">{{ $dsn->name }}</td>
                        <td class="py-3.5 px-4 text-slate-300">{{ $dsn->email }}</td>
                        <td class="py-3.5 px-4 text-slate-300 font-mono text-xs">{{ $dsn->kelas ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-300 text-xs">{{ $dsn->prodi ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.dosen.edit', $dsn->id) }}" class="p-2 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 rounded-lg transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.dosen.destroy', $dsn->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun dosen ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500">Tidak ada data akun dosen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $dosen->links() }}
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Data Mahasiswa')
@section('page_title', 'Kelola Data Mahasiswa')

@section('content')
<div class="glass-panel p-6 rounded-2xl shadow-xl mb-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <form action="{{ route('admin.mahasiswa.index') }}" method="GET" class="w-full flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama, NIM, atau UID..." 
                       class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
            </div>
            
            <div>
                <select name="kelas" class="bg-slate-900/60 border border-slate-800 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                    <option value="">Semua Kelas</option>
                    @foreach($allKelas as $kls)
                        <option value="{{ $kls }}" {{ request('kelas') == $kls ? 'selected' : '' }}>{{ $kls }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="prodi" class="bg-slate-900/60 border border-slate-800 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                    <option value="">Semua Prodi</option>
                    @foreach($allProdi as $prd)
                        <option value="{{ $prd }}" {{ request('prodi') == $prd ? 'selected' : '' }}>{{ $prd }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-slate-850 hover:bg-slate-800 text-slate-200 font-medium rounded-xl text-sm transition-all border border-slate-800">
                Filter
            </button>
            
            @if(request()->anyFilled(['search', 'kelas', 'prodi']))
                <a href="{{ route('admin.mahasiswa.index') }}" class="px-4 py-2.5 text-slate-400 hover:text-slate-200 text-sm transition-all">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('admin.mahasiswa.create') }}" class="flex-shrink-0 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-medium rounded-xl text-sm transition-all shadow-lg shadow-indigo-500/10">
            + Tambah Mahasiswa
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800 text-xs font-semibold text-slate-400 uppercase">
                    <th class="py-3 px-4">UID KTM (Scan)</th>
                    <th class="py-3 px-4">NIM</th>
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">Kelas</th>
                    <th class="py-3 px-4">Prodi</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40 text-sm">
                @forelse($mahasiswa as $mhs)
                    <tr class="hover:bg-slate-900/10 transition-colors">
                        <td class="py-2 px-4">
                            <input type="text" 
                                   value="{{ $mhs->uid_ktm }}" 
                                   data-nim="{{ $mhs->nim }}"
                                   placeholder="Scan KTM..."
                                   class="uid-input w-36 bg-slate-950 border border-slate-700 text-indigo-400 rounded-lg px-3 py-1.5 text-xs focus:border-indigo-500 transition-all focus:outline-none">
                        </td>
                        <td class="py-3.5 px-4 text-slate-200">{{ $mhs->nim }}</td>
                        <td class="py-3.5 px-4 font-medium text-white">{{ $mhs->nama }}</td>
                        <td class="py-3.5 px-4 text-slate-300">{{ $mhs->kelas }}</td>
                        <td class="py-3.5 px-4 text-slate-400">{{ $mhs->prodi }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.mahasiswa.edit', $mhs->uid_ktm) }}" class="p-2 text-indigo-400 hover:text-indigo-300 rounded-lg transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.mahasiswa.destroy', $mhs->uid_ktm) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-400 hover:text-red-300 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-500">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $mahasiswa->links() }}</div>
</div>

<script>
document.querySelectorAll('.uid-input').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let nim = this.getAttribute('data-nim');
            let uid = this.value;
            let el = this;

            fetch('{{ route("admin.mahasiswa.updateUid") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ nim: nim, uid_ktm: uid })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    el.classList.replace('border-slate-700', 'border-green-500');
                    setTimeout(() => el.classList.replace('border-green-500', 'border-slate-700'), 1500);
                } else {
                    alert(data.message);
                }
            });
        }
    });
});
</script>
@endsection
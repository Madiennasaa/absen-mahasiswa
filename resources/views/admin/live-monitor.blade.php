@extends('layouts.app')

@section('title', 'Live Monitor Absensi')
@section('page_title', 'Live Monitor Absensi (Real-time)')

@section('content')
<div class="glass-panel p-6 rounded-2xl shadow-xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-white">Log Pemindaian Terbaru</h3>
            <p class="text-sm text-slate-400 mt-1">Data diperbarui otomatis setiap 2 detik tanpa reload halaman.</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-semibold">
            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span>
            Terhubung
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800 text-xs font-semibold text-slate-400 uppercase">
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">NIM</th>
                    <th class="py-3 px-4">Kelas</th>
                    <th class="py-3 px-4">Program Studi</th>
                    <th class="py-3 px-4">Waktu Tap</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody id="live-table-body" class="divide-y divide-slate-800/40 text-sm">
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-500">Menghubungkan & mengambil data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.getElementById('live-table-body');

        function fetchLiveData() {
            fetch('{{ route("admin.live-data") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">Belum ada aktivitas tap kartu hari ini.</td>
                            </tr>
                        `;
                        return;
                    }

                    let html = '';
                    data.forEach(item => {
                        const statusClass = item.status === 'Hadir' 
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                            : 'bg-amber-500/10 text-amber-400 border border-amber-500/20';

                        html += `
                            <tr class="hover:bg-slate-900/10 transition-colors animate-fade-in">
                                <td class="py-3.5 px-4 font-medium text-white">${item.nama}</td>
                                <td class="py-3.5 px-4 text-slate-300">${item.nim}</td>
                                <td class="py-3.5 px-4 text-slate-400">${item.kelas}</td>
                                <td class="py-3.5 px-4 text-slate-400">${item.prodi}</td>
                                <td class="py-3.5 px-4 font-mono text-indigo-400">${item.waktu}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${statusClass}">
                                        ${item.status}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                    tableBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching live data:', error);
                });
        }

        // Poll every 2 seconds
        fetchLiveData();
        setInterval(fetchLiveData, 2000);
    });
</script>
@endsection

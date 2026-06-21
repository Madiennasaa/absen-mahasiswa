@extends('layouts.app')

@section('title', 'Rekap & Laporan Absensi')
@section('page_title', 'Rekap & Laporan Absensi')

@section('content')
{{-- Scope Info Badge --}}
<div class="mb-6 flex flex-wrap items-center gap-3">
    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20">
        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        <span class="text-xs font-semibold text-indigo-400">Kelas: {{ $scope['kelas'] }}</span>
    </div>
    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-500/10 border border-violet-500/20">
        <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <span class="text-xs font-semibold text-violet-400">Prodi: {{ $scope['prodi'] }}</span>
    </div>
    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800/60 border border-slate-700/40">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span class="text-xs text-slate-400">Hari aktif: <span class="text-white font-semibold">{{ $totalHariAktif }}</span> hari</span>
    </div>
</div>

{{-- Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Donut Chart: Persentase Kehadiran --}}
    <div class="glass-panel p-5 rounded-2xl shadow-xl flex flex-col justify-between">
        <h4 class="text-sm font-semibold text-slate-300 mb-4 text-center">Persentase Kehadiran Keseluruhan</h4>
        <div class="h-56 flex items-center justify-center">
            <canvas id="donutChart"></canvas>
        </div>
    </div>

    {{-- Bar Chart: Per Mahasiswa --}}
    <div class="glass-panel p-5 rounded-2xl shadow-xl flex flex-col justify-between col-span-1 lg:col-span-2">
        <h4 class="text-sm font-semibold text-slate-300 mb-4">Jumlah Hadir / Terlambat / Alpa per Mahasiswa</h4>
        <div class="h-56">
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 mb-8">
    {{-- Line Chart: Tren Harian --}}
    <div class="glass-panel p-5 rounded-2xl shadow-xl">
        <h4 class="text-sm font-semibold text-slate-300 mb-4">Tren Kehadiran Harian</h4>
        <div class="h-64">
            <canvas id="lineChart"></canvas>
        </div>
    </div>
</div>

{{-- Filter Section — tanpa filter kelas/prodi (sudah terkunci dari akun dosen) --}}
<div class="glass-panel p-6 rounded-2xl shadow-xl mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-6 border-b border-slate-800">
        <h3 class="text-base font-semibold text-white">Filter & Unduh Rekap</h3>

        {{-- Export Buttons --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('dosen.export.pdf', request()->query()) }}" class="flex items-center gap-2 px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 font-medium rounded-xl text-xs transition-all border border-rose-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh PDF
            </a>
            <a href="{{ route('dosen.export.excel', request()->query()) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-medium rounded-xl text-xs transition-all border border-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh Excel
            </a>
        </div>
    </div>

    <form action="{{ route('dosen.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Tanggal Mulai --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500 transition-colors">
        </div>

        {{-- Tanggal Akhir --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500 transition-colors">
        </div>

        {{-- Status Filter --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Status</label>
            <select name="status" class="w-full bg-slate-900/60 border border-slate-800 text-slate-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500 transition-colors">
                <option value="">Semua Status</option>
                <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                <option value="Terlambat" {{ request('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                <option value="Alpa" {{ request('status') == 'Alpa' ? 'selected' : '' }}>Alpa</option>
            </select>
        </div>

        {{-- Tombol Terapkan & Reset --}}
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl text-xs transition-all">
                Terapkan
            </button>
            @if(request()->anyFilled(['start_date', 'end_date', 'status']))
                <a href="{{ route('dosen.dashboard') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-xl transition-all border border-slate-700">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Tabel Rekap Absensi --}}
<div class="glass-panel p-6 rounded-2xl shadow-xl mb-8">
    <div class="flex items-center justify-between mb-4">
        <h4 class="text-sm font-semibold text-white">Log Rekap Absensi</h4>
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}" class="text-xs px-2.5 py-1 rounded-lg {{ !request('status') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-900' }}">Semua</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'Hadir']) }}" class="text-xs px-2.5 py-1 rounded-lg {{ request('status') == 'Hadir' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-400 hover:bg-slate-900' }}">Hadir</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'Terlambat']) }}" class="text-xs px-2.5 py-1 rounded-lg {{ request('status') == 'Terlambat' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 hover:bg-slate-900' }}">Terlambat</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'Alpa']) }}" class="text-xs px-2.5 py-1 rounded-lg {{ request('status') == 'Alpa' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'text-slate-400 hover:bg-slate-900' }}">Alpa</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800 text-xs font-semibold text-slate-400 uppercase">
                    <th class="py-3 px-4">Tanggal</th>
                    <th class="py-3 px-4">NIM</th>
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">Waktu Masuk</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40 text-sm">
                @forelse($paginatedRekap as $item)
                    <tr class="hover:bg-slate-900/30 transition-colors">
                        <td class="py-3 px-4 text-slate-300 font-mono text-xs">{{ $item['tanggal'] }}</td>
                        <td class="py-3 px-4 text-slate-300">{{ $item['nim'] }}</td>
                        <td class="py-3 px-4 font-medium text-white">{{ $item['nama'] }}</td>
                        <td class="py-3 px-4 text-slate-400 font-mono text-xs">{{ $item['waktu'] }}</td>
                        <td class="py-3 px-4">
                            @if($item['status'] === 'Hadir')
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Hadir</span>
                            @elseif($item['status'] === 'Terlambat')
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Terlambat</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">Alpa</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500">Tidak ada log absensi yang sesuai filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $paginatedRekap->links() }}
    </div>
</div>

{{-- Tabel Statistik per Mahasiswa --}}
<div class="glass-panel p-6 rounded-2xl shadow-xl">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h4 class="text-sm font-semibold text-white">Ringkasan Kehadiran per Mahasiswa</h4>
            <p class="text-xs text-slate-400 mt-1">Dihitung berdasarkan {{ $totalHariAktif }} hari aktif dalam filter waktu saat ini.</p>
        </div>
        <div>
            <form action="{{ route('dosen.dashboard') }}" method="GET" class="flex items-center gap-2">
                {{-- Pertahankan filter aktif --}}
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">

                <select name="sort_mhs" onchange="this.form.submit()" class="bg-slate-900 border border-slate-800 text-xs text-slate-300 rounded-xl px-3 py-2 focus:outline-none">
                    <option value="">Urutkan Kehadiran</option>
                    <option value="persentase_desc" {{ request('sort_mhs') == 'persentase_desc' ? 'selected' : '' }}>Persentase Tertinggi</option>
                    <option value="persentase_asc" {{ request('sort_mhs') == 'persentase_asc' ? 'selected' : '' }}>Persentase Terendah</option>
                </select>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800 text-xs font-semibold text-slate-400 uppercase">
                    <th class="py-3 px-4">NIM</th>
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">Hadir</th>
                    <th class="py-3 px-4">Terlambat</th>
                    <th class="py-3 px-4">Alpa</th>
                    <th class="py-3 px-4">Persentase Kehadiran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40 text-sm">
                @forelse($mahasiswaStats as $stat)
                    <tr class="hover:bg-slate-900/30 transition-colors">
                        <td class="py-3 px-4 text-slate-300 font-mono text-xs">{{ $stat['nim'] }}</td>
                        <td class="py-3 px-4 font-medium text-white">{{ $stat['nama'] }}</td>
                        <td class="py-3 px-4 text-emerald-400 font-semibold">{{ $stat['hadir'] }}</td>
                        <td class="py-3 px-4 text-amber-400 font-semibold">{{ $stat['terlambat'] }}</td>
                        <td class="py-3 px-4 text-rose-400 font-semibold">{{ $stat['alpa'] }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-24 bg-slate-900 rounded-full h-2 overflow-hidden border border-slate-800/80">
                                    <div class="h-full rounded-full bg-gradient-to-r {{ $stat['persentase'] >= 75 ? 'from-emerald-500 to-teal-400' : ($stat['persentase'] >= 50 ? 'from-amber-500 to-orange-400' : 'from-rose-500 to-red-400') }}"
                                         style="width: {{ min($stat['persentase'], 100) }}%"></div>
                                </div>
                                <span class="font-semibold text-white text-xs">{{ $stat['persentase'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">Tidak ada data mahasiswa di kelas & prodi Anda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const successColor = '#10b981';
        const warningColor = '#f59e0b';
        const dangerColor = '#f43f5e';
        const gridColor = 'rgba(51, 65, 85, 0.2)';
        const textColor = '#94a3b8';

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor, font: { family: 'Outfit', size: 11 } }
                }
            }
        };

        // 1. Donut Chart — Persentase keseluruhan
        const pieCtx = document.getElementById('donutChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Terlambat', 'Alpa'],
                datasets: [{
                    data: [{{ $chartPie['Hadir'] }}, {{ $chartPie['Terlambat'] }}, {{ $chartPie['Alpa'] }}],
                    backgroundColor: [successColor, warningColor, dangerColor],
                    borderColor: '#1e293b',
                    borderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                cutout: '70%',
            }
        });

        // 2. Bar Chart — Per mahasiswa
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barLabels = @json(array_keys($chartBar));
        const barHadir = [];
        const barTerlambat = [];
        const barAlpa = [];

        @json(array_values($chartBar)).forEach(item => {
            barHadir.push(item.Hadir);
            barTerlambat.push(item.Terlambat);
            barAlpa.push(item.Alpa);
        });

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [
                    { label: 'Hadir', data: barHadir, backgroundColor: successColor, borderRadius: 6 },
                    { label: 'Terlambat', data: barTerlambat, backgroundColor: warningColor, borderRadius: 6 },
                    { label: 'Alpa', data: barAlpa, backgroundColor: dangerColor, borderRadius: 6 }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, font: { size: 10 }, maxRotation: 45 } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } }
                }
            }
        });

        // 3. Line Chart — Tren harian
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: @json($chartLine['labels']),
                datasets: [
                    {
                        label: 'Hadir',
                        data: @json($chartLine['Hadir']),
                        borderColor: successColor,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3
                    },
                    {
                        label: 'Terlambat',
                        data: @json($chartLine['Terlambat']),
                        borderColor: warningColor,
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3
                    },
                    {
                        label: 'Alpa',
                        data: @json($chartLine['Alpa']),
                        borderColor: dangerColor,
                        backgroundColor: 'rgba(244, 63, 94, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } }
                }
            }
        });
    });
</script>
@endsection

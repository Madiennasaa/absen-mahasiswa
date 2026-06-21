@extends('layouts.app')

@section('title', 'Rekap & Laporan Absensi')
@section('page_title', 'Rekap & Laporan Absensi')

@section('content')
<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Donut Chart: Persentase -->
    <div class="glass-panel p-5 rounded-2xl shadow-xl flex flex-col justify-between">
        <h4 class="text-sm font-semibold text-slate-300 mb-4 text-center">Persentase Kehadiran Keseluruhan</h4>
        <div class="h-56 flex items-center justify-center">
            <canvas id="donutChart"></canvas>
        </div>
    </div>

    <!-- Bar Chart: Per Kelas -->
    <div class="glass-panel p-5 rounded-2xl shadow-xl flex flex-col justify-between col-span-1 lg:col-span-2">
        <h4 class="text-sm font-semibold text-slate-300 mb-4">Perbandingan Status per Kelas</h4>
        <div class="h-56">
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 mb-8">
    <!-- Line Chart: Tren Harian -->
    <div class="glass-panel p-5 rounded-2xl shadow-xl">
        <h4 class="text-sm font-semibold text-slate-300 mb-4">Tren Kehadiran Harian</h4>
        <div class="h-64">
            <canvas id="lineChart"></canvas>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="glass-panel p-6 rounded-2xl shadow-xl mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-6 border-b border-slate-800">
        <h3 class="text-base font-semibold text-white">Filter & Unduh Rekap</h3>
        
        <!-- Export Buttons -->
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

    <form action="{{ route('dosen.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Start Date -->
        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
        </div>
        
        <!-- End Date -->
        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-900/60 border border-slate-800 text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
        </div>

        <!-- Kelas -->
        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Kelas</label>
            <select name="kelas" class="w-full bg-slate-900/60 border border-slate-800 text-slate-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                <option value="">Semua Kelas</option>
                @foreach($allKelas as $kls)
                    <option value="{{ $kls }}" {{ request('kelas') == $kls ? 'selected' : '' }}>{{ $kls }}</option>
                @endforeach
            </select>
        </div>

        <!-- Prodi -->
        <div>
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Program Studi</label>
            <select name="prodi" class="w-full bg-slate-900/60 border border-slate-800 text-slate-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500">
                <option value="">Semua Prodi</option>
                @foreach($allProdi as $prd)
                    <option value="{{ $prd }}" {{ request('prodi') == $prd ? 'selected' : '' }}>{{ $prd }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter & Reset Buttons -->
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 py-2 bg-indigo-650 hover:bg-indigo-600 text-white font-medium rounded-xl text-xs transition-all border border-indigo-500/20">
                Terapkan
            </button>
            @if(request()->anyFilled(['start_date', 'end_date', 'kelas', 'prodi', 'status']))
                <a href="{{ route('dosen.dashboard') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-xl transition-all border border-slate-750">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Table Rekap Absensi -->
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
                    <th class="py-3 px-4">Kelas</th>
                    <th class="py-3 px-4">Waktu Absen</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40 text-sm">
                @forelse($paginatedRekap as $item)
                    <tr class="hover:bg-slate-900/10 transition-colors">
                        <td class="py-3 px-4 text-slate-300 font-mono text-xs">{{ $item['tanggal'] }}</td>
                        <td class="py-3 px-4 text-slate-300">{{ $item['nim'] }}</td>
                        <td class="py-3 px-4 font-medium text-white">{{ $item['nama'] }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $item['kelas'] }}</td>
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
                        <td colspan="6" class="py-8 text-center text-slate-500">Tidak ada log absensi yang sesuai filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $paginatedRekap->links() }}
    </div>
</div>

<!-- Mahasiswa Stats Table -->
<div class="glass-panel p-6 rounded-2xl shadow-xl">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h4 class="text-sm font-semibold text-white">Ringkasan Kehadiran per Mahasiswa</h4>
            <p class="text-xs text-slate-400 mt-1">Dihitung berdasarkan jumlah hari aktif dalam filter waktu saat ini.</p>
        </div>
        <div>
            <form action="{{ route('dosen.dashboard') }}" method="GET" class="flex items-center gap-2">
                <!-- Keep existing filters -->
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="kelas" value="{{ request('kelas') }}">
                <input type="hidden" name="prodi" value="{{ request('prodi') }}">
                
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
                    <tr class="hover:bg-slate-900/10 transition-colors">
                        <td class="py-3 px-4 text-slate-300 font-mono text-xs">{{ $stat['nim'] }}</td>
                        <td class="py-3 px-4 font-medium text-white">{{ $stat['nama'] }}</td>
                        <td class="py-3 px-4 text-emerald-400 font-semibold">{{ $stat['hadir'] }}</td>
                        <td class="py-3 px-4 text-amber-400 font-semibold">{{ $stat['terlambat'] }}</td>
                        <td class="py-3 px-4 text-rose-400 font-semibold">{{ $stat['alpa'] }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-24 bg-slate-900 rounded-full h-2 overflow-hidden border border-slate-800/80">
                                    <div class="h-full rounded-full bg-gradient-to-r {{ $stat['persentase'] >= 75 ? 'from-emerald-500 to-teal-400' : ($stat['persentase'] >= 50 ? 'from-amber-500 to-orange-400' : 'from-rose-500 to-red-400') }}" 
                                         style="width: {{ $stat['persentase'] }}%"></div>
                                </div>
                                <span class="font-semibold text-white text-xs">{{ $stat['persentase'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">Tidak ada ringkasan data mahasiswa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const primaryColor = '#4f46e5'; // indigo-600
        const accentColor = '#8b5cf6';  // violet-500
        const successColor = '#10b981'; // emerald-500
        const warningColor = '#f59e0b'; // amber-500
        const dangerColor = '#f43f5e';  // rose-500
        const gridColor = 'rgba(51, 65, 85, 0.2)'; // slate-700 with opacity
        const textColor = '#94a3b8'; // slate-400

        // Common Chart options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor, font: { family: 'Outfit', size: 11 } }
                }
            }
        };

        // 1. Donut Chart
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

        // 2. Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barKelas = @json(array_keys($chartBar));
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
                labels: barKelas,
                datasets: [
                    { label: 'Hadir', data: barHadir, backgroundColor: successColor, borderRadius: 6 },
                    { label: 'Terlambat', data: barTerlambat, backgroundColor: warningColor, borderRadius: 6 },
                    { label: 'Alpa', data: barAlpa, backgroundColor: dangerColor, borderRadius: 6 }
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

        // 3. Line Chart
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
                        backgroundColor: 'transparent',
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3
                    },
                    {
                        label: 'Terlambat',
                        data: @json($chartLine['Terlambat']),
                        borderColor: warningColor,
                        backgroundColor: 'transparent',
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3
                    },
                    {
                        label: 'Alpa',
                        data: @json($chartLine['Alpa']),
                        borderColor: dangerColor,
                        backgroundColor: 'transparent',
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

/**
 * DosenController — Dashboard dosen (read-only, scoped)
 *
 * Semua query di controller ini WAJIB di-scope ke kelas & prodi
 * yang tersimpan di akun dosen yang sedang login (users.kelas & users.prodi).
 * Dosen tidak bisa ganti-ganti kelas/prodi — data langsung terkunci.
 *
 * "Alpa" bukan data eksplisit di tabel absensi. Alpa dihitung sebagai:
 *   jumlah_hari_aktif - jumlah_record_absensi_mahasiswa
 * dalam rentang tanggal yang dipilih.
 */
class DosenController extends Controller
{
    /**
     * Ambil kelas & prodi dari user dosen yang sedang login.
     * Digunakan di seluruh method untuk scoping query.
     */
    private function getDosenScope(): array
    {
        $user = auth()->user();
        return [
            'kelas' => $user->kelas,
            'prodi' => $user->prodi,
        ];
    }

    /**
     * Halaman utama dosen: rekap absensi + grafik + statistik per mahasiswa.
     */
    public function index(Request $request)
    {
        $rekapInfo = $this->getRekapData($request);
        $rekapData = $rekapInfo['data'];
        $startDate = $rekapInfo['start_date'];
        $endDate = $rekapInfo['end_date'];
        $totalHariAktif = $rekapInfo['total_hari_aktif'];

        // --- Pagination untuk tabel rekap ---
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentPageItems = $rekapData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedRekap = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $rekapData->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // --- Chart: Pie/Donut — Persentase Hadir vs Terlambat vs Alpa ---
        $statusCounts = $rekapData->groupBy('status')->map(fn($items) => $items->count());
        $chartPie = [
            'Hadir' => $statusCounts->get('Hadir', 0),
            'Terlambat' => $statusCounts->get('Terlambat', 0),
            'Alpa' => $statusCounts->get('Alpa', 0),
        ];

        // --- Chart: Bar — Hadir/Terlambat/Alpa per mahasiswa ---
        $mahasiswaGroups = $rekapData->groupBy('nim');
        $chartBar = [];
        foreach ($mahasiswaGroups as $nim => $items) {
            $mhs = $items->first();
            $counts = $items->groupBy('status')->map(fn($g) => $g->count());
            $chartBar[$mhs['nama']] = [
                'Hadir' => $counts->get('Hadir', 0),
                'Terlambat' => $counts->get('Terlambat', 0),
                'Alpa' => $counts->get('Alpa', 0),
            ];
        }

        // --- Chart: Line — Tren kehadiran harian ---
        $dateGroups = $rekapData->groupBy('tanggal')->sortKeys();
        $chartLine = [
            'labels' => [],
            'Hadir' => [],
            'Terlambat' => [],
            'Alpa' => [],
        ];
        foreach ($dateGroups as $date => $items) {
            $chartLine['labels'][] = Carbon::parse($date)->format('d M');
            $counts = $items->groupBy('status')->map(fn($g) => $g->count());
            $chartLine['Hadir'][] = $counts->get('Hadir', 0);
            $chartLine['Terlambat'][] = $counts->get('Terlambat', 0);
            $chartLine['Alpa'][] = $counts->get('Alpa', 0);
        }

        // --- Statistik per mahasiswa ---
        $mahasiswaStats = collect();
        foreach ($mahasiswaGroups as $nim => $items) {
            $mhs = $items->first();
            $counts = $items->groupBy('status')->map(fn($g) => $g->count());
            $hadir = $counts->get('Hadir', 0);
            $terlambat = $counts->get('Terlambat', 0);
            $alpa = $counts->get('Alpa', 0);

            // Persentase kehadiran = (Hadir + Terlambat) / total_hari_aktif * 100
            $persentase = $totalHariAktif > 0
                ? round((($hadir + $terlambat) / $totalHariAktif) * 100, 1)
                : 0;

            $mahasiswaStats->push([
                'nim' => $nim,
                'nama' => $mhs['nama'],
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'alpa' => $alpa,
                'persentase' => $persentase,
            ]);
        }

        // Sorting statistik mahasiswa
        if ($request->filled('sort_mhs')) {
            $mahasiswaStats = match ($request->sort_mhs) {
                'persentase_asc' => $mahasiswaStats->sortBy('persentase')->values(),
                'persentase_desc' => $mahasiswaStats->sortByDesc('persentase')->values(),
                default => $mahasiswaStats,
            };
        }

        $scope = $this->getDosenScope();

        return view('dosen.dashboard', compact(
            'paginatedRekap',
            'chartPie',
            'chartBar',
            'chartLine',
            'mahasiswaStats',
            'startDate',
            'endDate',
            'totalHariAktif',
            'scope'
        ));
    }

    /**
     * Export rekap absensi ke PDF (ter-scope ke kelas+prodi dosen).
     */
    public function exportPdf(Request $request)
    {
        $rekapInfo = $this->getRekapData($request);
        $rekapData = $rekapInfo['data'];
        $startDate = $rekapInfo['start_date'];
        $endDate = $rekapInfo['end_date'];
        $scope = $this->getDosenScope();

        $pdf = Pdf::loadView('exports.rekap-pdf', compact('rekapData', 'startDate', 'endDate', 'scope'));
        $pdf->setPaper('a4', 'landscape');

        $filename = "rekap-absensi-{$scope['kelas']}-{$startDate}-to-{$endDate}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export rekap absensi ke Excel (ter-scope ke kelas+prodi dosen).
     */
    public function exportExcel(Request $request)
    {
        $rekapInfo = $this->getRekapData($request);
        $rekapData = $rekapInfo['data'];
        $startDate = $rekapInfo['start_date'];
        $endDate = $rekapInfo['end_date'];
        $scope = $this->getDosenScope();

        $filename = "rekap-absensi-{$scope['kelas']}-{$startDate}-to-{$endDate}.xlsx";

        return Excel::download(new AbsensiExport($rekapData), $filename);
    }

    /**
     * Ambil dan hitung data rekap absensi, SELALU di-scope ke kelas+prodi dosen.
     *
     * Logic Alpa:
     * - Untuk setiap tanggal dalam rentang, untuk setiap mahasiswa di kelas+prodi dosen:
     *   - Jika ada record di tabel absensi → status = Hadir/Terlambat (dari DB)
     *   - Jika tidak ada record → status = Alpa (dihitung, bukan dari DB)
     *
     * Filter status (Hadir/Terlambat/Alpa) diterapkan SETELAH kalkulasi Alpa.
     */
    private function getRekapData(Request $request): array
    {
        $scope = $this->getDosenScope();

        // Parsing tanggal
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        $startDate = $startDateInput ? Carbon::parse($startDateInput) : Carbon::today();
        $endDate = $endDateInput ? Carbon::parse($endDateInput) : Carbon::today();

        // Swap jika terbalik
        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        // Bangun daftar tanggal aktif dalam rentang (semua hari, termasuk weekend)
        $dates = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }
        $totalHariAktif = count($dates);

        // Query mahasiswa — WAJIB di-scope ke kelas+prodi dosen
        $mahasiswas = Mahasiswa::where('kelas', $scope['kelas'])
            ->where('prodi', $scope['prodi'])
            ->orderBy('nama')
            ->get();

        // Query absensi dalam rentang — hanya untuk mahasiswa di kelas+prodi dosen
        $mahasiswaUids = $mahasiswas->pluck('uid_ktm');
        $absensi = Absensi::whereIn('uid_ktm', $mahasiswaUids)
            ->whereBetween('waktu_masuk', [
                $startDate->copy()->startOfDay()->toDateTimeString(),
                $endDate->copy()->endOfDay()->toDateTimeString(),
            ])
            ->get()
            ->groupBy(function ($item) {
                return $item->uid_ktm . '_' . $item->waktu_masuk->format('Y-m-d');
            });

        // Bangun data rekap: semua tanggal x semua mahasiswa
        $rekap = [];
        foreach ($dates as $date) {
            foreach ($mahasiswas as $mhs) {
                $key = $mhs->uid_ktm . '_' . $date;

                if (isset($absensi[$key])) {
                    $record = $absensi[$key]->first();
                    $status = $record->status;
                    $waktu = $record->waktu_masuk->format('H:i:s');
                } else {
                    $status = 'Alpa';
                    $waktu = '-';
                }

                $rekap[] = [
                    'tanggal' => $date,
                    'nim' => $mhs->nim,
                    'nama' => $mhs->nama,
                    'kelas' => $mhs->kelas,
                    'prodi' => $mhs->prodi,
                    'waktu' => $waktu,
                    'status' => $status,
                ];
            }
        }

        // Filter status jika diminta
        if ($request->filled('status')) {
            $statusFilter = $request->status;
            $rekap = array_filter($rekap, fn($item) => $item['status'] === $statusFilter);
        }

        return [
            'data' => collect(array_values($rekap)),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_hari_aktif' => $totalHariAktif,
        ];
    }

    /**
     * Halaman live monitor — menampilkan tap kartu real-time (ter-scope).
     */
    public function liveMonitor()
    {
        $scope = $this->getDosenScope();
        return view('dosen.live-monitor', compact('scope'));
    }

    /**
     * Data JSON untuk live monitor — WAJIB di-scope ke kelas+prodi dosen.
     */
    public function liveData()
    {
        $scope = $this->getDosenScope();

        $recent = Absensi::with('mahasiswa')
            ->whereHas('mahasiswa', function ($q) use ($scope) {
                $q->where('kelas', $scope['kelas'])
                  ->where('prodi', $scope['prodi']);
            })
            ->whereDate('waktu_masuk', Carbon::today())
            ->orderBy('waktu_masuk', 'desc')
            ->take(15)
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->mahasiswa->nama ?? 'Unknown',
                    'nim' => $item->mahasiswa->nim ?? '-',
                    'kelas' => $item->mahasiswa->kelas ?? '-',
                    'prodi' => $item->mahasiswa->prodi ?? '-',
                    'waktu' => $item->waktu_masuk->format('H:i:s'),
                    'status' => $item->status,
                ];
            });

        return response()->json($recent);
    }
}

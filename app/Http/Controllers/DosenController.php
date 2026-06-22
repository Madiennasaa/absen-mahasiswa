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
 * DosenController — Dashboard dosen (read-only, scoped ke kelas+prodi)
 *
 * Alpa = hari aktif tanpa record absensi (bukan dari DB, dihitung manual).
 * Status dari DB selalu di-normalize dengan ucfirst(strtolower()) supaya
 * konsisten meski ada inkonsistensi case di database.
 */
class DosenController extends Controller
{
    /**
     * Ambil kelas & prodi dari user dosen yang sedang login.
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
     * Normalize status dari DB → selalu Title Case
     * 'hadir' → 'Hadir', 'TERLAMBAT' → 'Terlambat', dst.
     */
    private function normalizeStatus(string $status): string
    {
        return ucfirst(strtolower($status));
    }

    /**
     * Halaman utama dosen: rekap + grafik + statistik per mahasiswa.
     */
    public function index(Request $request)
    {
        $rekapInfo      = $this->getRekapData($request);
        $rekapData      = $rekapInfo['data'];
        $startDate      = $rekapInfo['start_date'];
        $endDate        = $rekapInfo['end_date'];
        $totalHariAktif = $rekapInfo['total_hari_aktif'];

        // --- Pagination tabel rekap ---
        $currentPage      = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage          = 15;
        $currentPageItems = $rekapData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedRekap   = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $rekapData->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // --- Chart Donut: Persentase keseluruhan ---
        $statusCounts = $rekapData->groupBy('status')->map(fn($items) => $items->count());
        $chartPie = [
            'Hadir'     => $statusCounts->get('Hadir', 0),
            'Terlambat' => $statusCounts->get('Terlambat', 0),
            'Alpa'      => $statusCounts->get('Alpa', 0),
        ];

        // --- Chart Bar: Hadir/Terlambat/Alpa per mahasiswa ---
        $mahasiswaGroups = $rekapData->groupBy('nim');
        $chartBar        = [];
        foreach ($mahasiswaGroups as $nim => $items) {
            $mhs    = $items->first();
            $counts = $items->groupBy('status')->map(fn($g) => $g->count());
            $chartBar[$mhs['nama']] = [
                'Hadir'     => $counts->get('Hadir', 0),
                'Terlambat' => $counts->get('Terlambat', 0),
                'Alpa'      => $counts->get('Alpa', 0),
            ];
        }

        // --- Chart Line: Tren harian ---
        $dateGroups = $rekapData->groupBy('tanggal')->sortKeys();
        $chartLine  = [
            'labels'     => [],
            'Hadir'      => [],
            'Terlambat'  => [],
            'Alpa'       => [],
        ];
        foreach ($dateGroups as $date => $items) {
            $chartLine['labels'][]    = Carbon::parse($date)->format('d M');
            $counts                   = $items->groupBy('status')->map(fn($g) => $g->count());
            $chartLine['Hadir'][]     = $counts->get('Hadir', 0);
            $chartLine['Terlambat'][] = $counts->get('Terlambat', 0);
            $chartLine['Alpa'][]      = $counts->get('Alpa', 0);
        }

        // --- Statistik per mahasiswa ---
        $mahasiswaStats = collect();
        foreach ($mahasiswaGroups as $nim => $items) {
            $mhs      = $items->first();
            $counts   = $items->groupBy('status')->map(fn($g) => $g->count());
            $hadir      = $counts->get('Hadir', 0);
            $terlambat  = $counts->get('Terlambat', 0);
            $alpa       = $counts->get('Alpa', 0);

            $persentase = $totalHariAktif > 0
                ? round((($hadir + $terlambat) / $totalHariAktif) * 100, 1)
                : 0;

            // Total menit terlambat per mahasiswa
            $totalMenitMhs = $items
                ->where('status', 'Terlambat')
                ->sum('keterlambatan_menit');

            $mahasiswaStats->push([
                'nim'                    => $nim,
                'nama'                   => $mhs['nama'],
                'hadir'                  => $hadir,
                'terlambat'              => $terlambat,
                'alpa'                   => $alpa,
                'persentase'             => $persentase,
                'total_menit_terlambat'  => $totalMenitMhs,
            ]);
        }

        // Sorting
        if ($request->filled('sort_mhs')) {
            $mahasiswaStats = match ($request->sort_mhs) {
                'persentase_asc'  => $mahasiswaStats->sortBy('persentase')->values(),
                'persentase_desc' => $mahasiswaStats->sortByDesc('persentase')->values(),
                default           => $mahasiswaStats,
            };
        }

        $scope = $this->getDosenScope();

        $mahasiswaList = Mahasiswa::where('kelas', $scope['kelas'])
            ->where('prodi', $scope['prodi'])
            ->orderBy('nama')
            ->get();

        // Akumulasi keterlambatan keseluruhan
        $totalKeterlambatanMenit = $rekapData
            ->where('status', 'Terlambat')
            ->sum('keterlambatan_menit');

        return view('dosen.dashboard', compact(
            'paginatedRekap',
            'chartPie',
            'chartBar',
            'chartLine',
            'mahasiswaStats',
            'startDate',
            'endDate',
            'totalHariAktif',
            'scope',
            'mahasiswaList',
            'totalKeterlambatanMenit'
        ));
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $rekapInfo = $this->getRekapData($request);
        $rekapData = $rekapInfo['data'];
        $startDate = $rekapInfo['start_date'];
        $endDate   = $rekapInfo['end_date'];
        $scope     = $this->getDosenScope();

        $pdf = Pdf::loadView('exports.rekap-pdf', compact('rekapData', 'startDate', 'endDate', 'scope'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("rekap-absensi-{$scope['kelas']}-{$startDate}-to-{$endDate}.pdf");
    }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        $rekapInfo = $this->getRekapData($request);
        $rekapData = $rekapInfo['data'];
        $startDate = $rekapInfo['start_date'];
        $endDate   = $rekapInfo['end_date'];
        $scope     = $this->getDosenScope();

        return Excel::download(
            new AbsensiExport($rekapData),
            "rekap-absensi-{$scope['kelas']}-{$startDate}-to-{$endDate}.xlsx"
        );
    }

    /**
     * Core logic: bangun data rekap lengkap (termasuk Alpa yang dihitung manual).
     *
     * FIX: status dari DB di-normalize pakai normalizeStatus()
     *      supaya ga ada mismatch case ('hadir' vs 'Hadir').
     */
    private function getRekapData(Request $request): array
    {
        $scope = $this->getDosenScope();

        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        // Default rentang: 30 hari terakhir (bukan cuma hari ini),
        // supaya dashboard & chart langsung menampilkan data yang representatif.
        $startDate = $startDateInput ? Carbon::parse($startDateInput) : Carbon::today()->subDays(29);
        $endDate   = $endDateInput   ? Carbon::parse($endDateInput)   : Carbon::today();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        // Bangun list tanggal aktif
        $dates   = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }
        $totalHariAktif = count($dates);

        // Query mahasiswa (scoped)
        $mahasiswas = Mahasiswa::where('kelas', $scope['kelas'])
            ->where('prodi', $scope['prodi'])
            ->orderBy('nama')
            ->get();

        // Query absensi dalam rentang
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

        // Bangun rekap: semua tanggal × semua mahasiswa
        $rekap = [];
        foreach ($dates as $date) {
            foreach ($mahasiswas as $mhs) {
                $key = $mhs->uid_ktm . '_' . $date;

                if (isset($absensi[$key])) {
                    $record = $absensi[$key]->first();

                    // ✅ FIX: normalize status supaya selalu Title Case
                    $status             = $this->normalizeStatus($record->status);
                    $waktu              = $record->waktu_masuk->format('H:i:s');
                    $keterlambatanMenit = $record->keterlambatan_menit;
                } else {
                    $status             = 'Alpa';
                    $waktu              = '-';
                    $keterlambatanMenit = null;
                }

                $rekap[] = [
                    'tanggal'            => $date,
                    'nim'                => $mhs->nim,
                    'nama'               => $mhs->nama,
                    'kelas'              => $mhs->kelas,
                    'prodi'              => $mhs->prodi,
                    'waktu'              => $waktu,
                    'status'             => $status,
                    'keterlambatan_menit'=> $keterlambatanMenit,
                ];
            }
        }

        // Filter status
        if ($request->filled('status')) {
            $statusFilter = $request->status;
            $rekap = array_filter($rekap, fn($item) => $item['status'] === $statusFilter);
        }

        // Filter mahasiswa
        if ($request->filled('nim')) {
            $nimFilter = $request->nim;
            $rekap = array_filter($rekap, fn($item) => $item['nim'] === $nimFilter);
        }

        return [
            'data'            => collect(array_values($rekap)),
            'start_date'      => $startDate->format('Y-m-d'),
            'end_date'        => $endDate->format('Y-m-d'),
            'total_hari_aktif'=> $totalHariAktif,
        ];
    }

    /**
     * Live monitor view
     */
    public function liveMonitor()
    {
        $scope = $this->getDosenScope();
        return view('dosen.live-monitor', compact('scope'));
    }

    /**
     * JSON untuk live monitor (polling/SSE dari frontend)
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
                    'nama'               => $item->mahasiswa->nama ?? 'Unknown',
                    'nim'                => $item->mahasiswa->nim ?? '-',
                    'kelas'              => $item->mahasiswa->kelas ?? '-',
                    'prodi'              => $item->mahasiswa->prodi ?? '-',
                    'waktu'              => $item->waktu_masuk->format('H:i:s'),
                    'status'             => $this->normalizeStatus($item->status),
                    'keterlambatan_menit'=> $item->keterlambatan_menit,
                ];
            });

        return response()->json($recent);
    }
}
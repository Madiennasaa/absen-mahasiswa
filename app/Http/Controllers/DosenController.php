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
 *
 * FIX v2:
 * - whereBetween diganti whereDate agar tidak sensitif terhadap timezone jam
 * - $chartPie & $totalKeterlambatanMenit dihitung dari data TANPA filter
 *   status/nim, supaya overview cards selalu menampilkan total keseluruhan
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
        // --- Data TERFILTER (status + nim) untuk tabel log & chart ---
        $rekapInfo      = $this->getRekapData($request);
        $rekapData      = $rekapInfo['data'];
        $startDate      = $rekapInfo['start_date'];
        $endDate        = $rekapInfo['end_date'];
        $totalHariAktif = $rekapInfo['total_hari_aktif'];

        // --- Data TANPA filter status/nim untuk overview cards ---
        // Supaya card Total Hadir/Terlambat/Alpa/Menit tidak terpengaruh
        // ketika user sedang memfilter tabel berdasarkan status atau mahasiswa tertentu.
        $rekapInfoAll = $this->getRekapData(new \Illuminate\Http\Request([
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            // sengaja tidak pass 'status' dan 'nim'
        ]));
        $rekapDataAll = $rekapInfoAll['data'];

        // --- Pagination tabel rekap (dari data terfilter) ---
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

        // --- Chart Donut & Cards: dari data ALL (tidak terfilter) ---
        $statusCountsAll = $rekapDataAll->groupBy('status')->map(fn($items) => $items->count());
        $chartPie = [
            'Hadir'     => $statusCountsAll->get('Hadir', 0),
            'Terlambat' => $statusCountsAll->get('Terlambat', 0),
            'Alpa'      => $statusCountsAll->get('Alpa', 0),
        ];

        // Akumulasi keterlambatan keseluruhan (dari data ALL)
        $totalKeterlambatanMenit = $rekapDataAll
            ->where('status', 'Terlambat')
            ->sum('keterlambatan_menit');

        // --- Chart Bar: Hadir/Terlambat/Alpa per mahasiswa (dari data terfilter) ---
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

        // --- Chart Line: Tren harian (dari data terfilter) ---
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

        // --- Statistik per mahasiswa (dari data terfilter) ---
        $mahasiswaStats = collect();
        foreach ($mahasiswaGroups as $nim => $items) {
            $mhs      = $items->first();
            $counts   = $items->groupBy('status')->map(fn($g) => $g->count());
            $hadir     = $counts->get('Hadir', 0);
            $terlambat = $counts->get('Terlambat', 0);
            $alpa      = $counts->get('Alpa', 0);

            $persentase = $totalHariAktif > 0
                ? round((($hadir + $terlambat) / $totalHariAktif) * 100, 1)
                : 0;

            $totalMenitMhs = $items
                ->where('status', 'Terlambat')
                ->sum('keterlambatan_menit');

            $mahasiswaStats->push([
                'nim'                   => $nim,
                'nama'                  => $mhs['nama'],
                'hadir'                 => $hadir,
                'terlambat'             => $terlambat,
                'alpa'                  => $alpa,
                'persentase'            => $persentase,
                'total_menit_terlambat' => $totalMenitMhs,
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
     * FIX v2:
     * - Ganti whereBetween → whereDate agar tidak sensitif timezone jam (UTC vs WIB).
     *   whereBetween dengan toDateTimeString() bisa miss data yang masuk
     *   setelah jam 17:00 WIB jika server/DB berjalan di UTC.
     */
    private function getRekapData(Request $request): array
    {
        $scope = $this->getDosenScope();

        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        // Default rentang: 30 hari terakhir
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

        // Query mahasiswa (scoped ke kelas + prodi dosen)
        $mahasiswas = Mahasiswa::where('kelas', $scope['kelas'])
            ->where('prodi', $scope['prodi'])
            ->orderBy('nama')
            ->get();

        // Query absensi dalam rentang
        // ✅ FIX: pakai whereDate (membandingkan bagian tanggal saja)
        //         bukan whereBetween dengan datetime string,
        //         agar tidak ada data baru yang terlewat karena perbedaan timezone.
        $mahasiswaUids = $mahasiswas->pluck('uid_ktm');
        $absensi = Absensi::whereIn('uid_ktm', $mahasiswaUids)
            ->whereDate('waktu_masuk', '>=', $startDate->format('Y-m-d'))
            ->whereDate('waktu_masuk', '<=', $endDate->format('Y-m-d'))
            ->get()
            ->groupBy(function ($item) {
                return $item->uid_ktm . '_' . Carbon::parse($item->waktu_masuk)->format('Y-m-d');
            });

        // Bangun rekap: semua tanggal × semua mahasiswa
        $rekap = [];
        foreach ($dates as $date) {
            foreach ($mahasiswas as $mhs) {
                $key = $mhs->uid_ktm . '_' . $date;

                if (isset($absensi[$key])) {
                    $record = $absensi[$key]->first();

                    $status             = $this->normalizeStatus($record->status);
                    $waktu              = Carbon::parse($record->waktu_masuk)->format('H:i:s');
                    $keterlambatanMenit = $record->keterlambatan_menit;
                } else {
                    $status             = 'Alpa';
                    $waktu              = '-';
                    $keterlambatanMenit = null;
                }

                $rekap[] = [
                    'tanggal'             => $date,
                    'nim'                 => $mhs->nim,
                    'nama'                => $mhs->nama,
                    'kelas'               => $mhs->kelas,
                    'prodi'               => $mhs->prodi,
                    'waktu'               => $waktu,
                    'status'              => $status,
                    'keterlambatan_menit' => $keterlambatanMenit,
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
            'data'             => collect(array_values($rekap)),
            'start_date'       => $startDate->format('Y-m-d'),
            'end_date'         => $endDate->format('Y-m-d'),
            'total_hari_aktif' => $totalHariAktif,
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

        $recent = \App\Models\RiwayatScan::where(function ($query) use ($scope) {
                $query->where(function ($q) use ($scope) {
                    $q->where('kelas', $scope['kelas'])
                      ->where('prodi', $scope['prodi']);
                })->orWhere('kelas', '-');
            })
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get()
            ->map(function ($item) {
                return [
                    'nama'                => $item->nama ?? 'Unknown',
                    'nim'                 => $item->nim ?? '-',
                    'kelas'               => $item->kelas ?? '-',
                    'prodi'               => $item->prodi ?? '-',
                    'waktu'               => Carbon::parse($item->created_at)->format('H:i:s'),
                    'status'              => $this->normalizeStatus($item->status),
                    'keterlambatan_menit' => null,
                ];
            });

        return response()->json($recent);
    }
}
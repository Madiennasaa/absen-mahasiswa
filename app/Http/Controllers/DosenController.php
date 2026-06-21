<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $rekapInfo = $this->getRekapData($request);
        $rekapData = $rekapInfo['data'];
        $startDate = $rekapInfo['start_date'];
        $endDate = $rekapInfo['end_date'];

        // Pagination for UI view (we calculate on collection directly for simplicity)
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = $rekapData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedRekap = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $rekapData->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Chart Data Agregations
        // 1. Pie/Donut Chart: Hadir vs Terlambat vs Alpa
        $statusCounts = $rekapData->groupBy('status')->map(fn($item) => $item->count());
        $chartPie = [
            'Hadir' => $statusCounts->get('Hadir', 0),
            'Terlambat' => $statusCounts->get('Terlambat', 0),
            'Alpa' => $statusCounts->get('Alpa', 0)
        ];

        // 2. Bar Chart: Status counts per Kelas
        $kelasGroups = $rekapData->groupBy('kelas');
        $chartBar = [];
        foreach ($kelasGroups as $kelas => $items) {
            $counts = $items->groupBy('status')->map(fn($group) => $group->count());
            $chartBar[$kelas] = [
                'Hadir' => $counts->get('Hadir', 0),
                'Terlambat' => $counts->get('Terlambat', 0),
                'Alpa' => $counts->get('Alpa', 0)
            ];
        }

        // 3. Line Chart: Daily attendance trends
        $dateGroups = $rekapData->groupBy('tanggal')->sortKeys();
        $chartLine = [
            'labels' => [],
            'Hadir' => [],
            'Terlambat' => [],
            'Alpa' => []
        ];
        foreach ($dateGroups as $date => $items) {
            $formattedDate = Carbon::parse($date)->format('d M');
            $chartLine['labels'][] = $formattedDate;
            $counts = $items->groupBy('status')->map(fn($group) => $group->count());
            $chartLine['Hadir'][] = $counts->get('Hadir', 0);
            $chartLine['Terlambat'][] = $counts->get('Terlambat', 0);
            $chartLine['Alpa'][] = $counts->get('Alpa', 0);
        }

        // 4. Per Mahasiswa Stats
        $mahasiswaGroups = $rekapData->groupBy('nim');
        $mahasiswaStats = [];
        foreach ($mahasiswaGroups as $nim => $items) {
            $mhs = $items->first();
            $counts = $items->groupBy('status')->map(fn($group) => $group->count());
            $hadir = $counts->get('Hadir', 0);
            $terlambat = $counts->get('Terlambat', 0);
            $alpa = $counts->get('Alpa', 0);
            $totalDays = $items->count();
            
            $percentage = $totalDays > 0 ? round((($hadir + $terlambat) / $totalDays) * 100, 2) : 0;

            $mahasiswaStats[] = [
                'nim' => $nim,
                'nama' => $mhs['nama'],
                'kelas' => $mhs['kelas'],
                'prodi' => $mhs['prodi'],
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'alpa' => $alpa,
                'total' => $totalDays,
                'persentase' => $percentage
            ];
        }
        $mahasiswaStats = collect($mahasiswaStats);

        // Sorting & pagination for mahasiswa stats
        if ($request->filled('sort_mhs')) {
            $sort = $request->sort_mhs;
            if ($sort === 'persentase_asc') {
                $mahasiswaStats = $mahasiswaStats->sortBy('persentase');
            } elseif ($sort === 'persentase_desc') {
                $mahasiswaStats = $mahasiswaStats->sortByDesc('persentase');
            }
        }
        
        $allKelas = Mahasiswa::select('kelas')->distinct()->pluck('kelas');
        $allProdi = Mahasiswa::select('prodi')->distinct()->pluck('prodi');

        return view('dosen.dashboard', compact(
            'paginatedRekap',
            'chartPie',
            'chartBar',
            'chartLine',
            'mahasiswaStats',
            'allKelas',
            'allProdi',
            'startDate',
            'endDate'
        ));
    }

    public function exportPdf(Request $request)
    {
        $rekapInfo = $this->getRekapData($request);
        $rekapData = $rekapInfo['data'];
        $startDate = $rekapInfo['start_date'];
        $endDate = $rekapInfo['end_date'];

        $pdf = Pdf::loadView('exports.rekap-pdf', compact('rekapData', 'startDate', 'endDate'));
        return $pdf->download("rekap-absensi-{$startDate}-to-{$endDate}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $rekapInfo = $this->getRekapData($request);
        $rekapData = $rekapInfo['data'];
        $startDate = $rekapInfo['start_date'];
        $endDate = $rekapInfo['end_date'];

        return Excel::download(new AbsensiExport($rekapData), "rekap-absensi-{$startDate}-to-{$endDate}.xlsx");
    }

    private function getRekapData(Request $request)
    {
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        // Default to today if no date range is provided
        $startDate = $startDateInput ? Carbon::parse($startDateInput) : Carbon::today();
        $endDate = $endDateInput ? Carbon::parse($endDateInput) : Carbon::today();

        if ($startDate->gt($endDate)) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        // Get all dates in range
        $dates = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Get mahasiswa with filters
        $mahasiswaQuery = Mahasiswa::query();
        if ($request->filled('kelas')) {
            $mahasiswaQuery->where('kelas', $request->kelas);
        }
        if ($request->filled('prodi')) {
            $mahasiswaQuery->where('prodi', $request->prodi);
        }
        $mahasiswas = $mahasiswaQuery->get();

        // Get all attendance records in range
        $absensi = Absensi::whereBetween('waktu_masuk', [
            $startDate->startOfDay()->toDateTimeString(),
            $endDate->endOfDay()->toDateTimeString()
        ])->get()->groupBy(function($item) {
            return $item->uid_ktm . '_' . $item->waktu_masuk->format('Y-m-d');
        });

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

        // Filter by status if requested
        if ($request->filled('status')) {
            $statusFilter = $request->status;
            $rekap = array_filter($rekap, function($item) use ($statusFilter) {
                return $item['status'] === $statusFilter;
            });
        }

        return [
            'data' => collect(array_values($rekap)),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];
    }

    public function liveMonitor()
    {
        return view('dosen.live-monitor');
    }

    public function liveData()
    {
        $recent = Absensi::with('mahasiswa')
            ->orderBy('waktu_masuk', 'desc')
            ->take(10)
            ->get()
            ->map(function($item) {
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $totalMahasiswa = Mahasiswa::count();
        $totalDosen = User::where('role', 'dosen')->count();
        $absenHariIni = Absensi::whereDate('waktu_masuk', Carbon::today())->count();
        $terlambatHariIni = Absensi::whereDate('waktu_masuk', Carbon::today())
            ->where('status', 'Terlambat')
            ->count();

        $recentAbsensi = Absensi::with('mahasiswa')
            ->orderBy('waktu_masuk', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalMahasiswa',
            'totalDosen',
            'absenHariIni',
            'terlambatHariIni',
            'recentAbsensi'
        ));
    }

    // CRUD Mahasiswa
    public function mahasiswaIndex(Request $request)
    {
        $query = Mahasiswa::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('uid_ktm', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('prodi')) {
            $query->where('prodi', $request->prodi);
        }

        $mahasiswa = $query->paginate(10)->withQueryString();

        $allKelas = Mahasiswa::select('kelas')->distinct()->pluck('kelas');
        $allProdi = Mahasiswa::select('prodi')->distinct()->pluck('prodi');

        return view('admin.mahasiswa.index', compact('mahasiswa', 'allKelas', 'allProdi'));
    }

    public function mahasiswaCreate()
    {
        return view('admin.mahasiswa.create');
    }

    public function mahasiswaStore(Request $request)
    {
        $validated = $request->validate([
            'uid_ktm' => 'required|string|max:36|unique:mahasiswa,uid_ktm|regex:/^[A-F0-9]+$/',
            'nim' => 'required|string|max:20|unique:mahasiswa,nim',
            'nama' => 'required|string|max:100',
            'kelas' => 'required|string|max:10',
            'prodi' => 'required|string|max:50',
        ]);

        Mahasiswa::create($validated);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function mahasiswaEdit($uid_ktm)
    {
        $mahasiswa = Mahasiswa::findOrFail($uid_ktm);
        return view('admin.mahasiswa.edit', compact('mahasiswa'));
    }

    public function mahasiswaUpdate(Request $request, $uid_ktm)
    {
        $mahasiswa = Mahasiswa::findOrFail($uid_ktm);

        $validated = $request->validate([
            'uid_ktm' => 'required|string|max:36|regex:/^[A-F0-9]+$/|unique:mahasiswa,uid_ktm,' . $mahasiswa->uid_ktm . ',uid_ktm',
            'nim' => 'required|string|max:20|unique:mahasiswa,nim,' . $mahasiswa->uid_ktm . ',uid_ktm',
            'nama' => 'required|string|max:100',
            'kelas' => 'required|string|max:10',
            'prodi' => 'required|string|max:50',
        ]);

        $mahasiswa->update($validated);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function mahasiswaDestroy($uid_ktm)
    {
        $mahasiswa = Mahasiswa::findOrFail($uid_ktm);
        
        // Delete related absensi records first
        $mahasiswa->absensi()->delete();
        $mahasiswa->delete();

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus.');
    }

    // CRUD Dosen
    public function dosenIndex()
    {
        $dosen = User::where('role', 'dosen')->paginate(10);
        return view('admin.dosen.index', compact('dosen'));
    }

    public function dosenCreate()
    {
        $allKelas = Mahasiswa::select('kelas')->distinct()->pluck('kelas');
        $allProdi = Mahasiswa::select('prodi')->distinct()->pluck('prodi');
        return view('admin.dosen.create', compact('allKelas', 'allProdi'));
    }

    public function dosenStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'kelas' => 'required|string|max:10',
            'prodi' => 'required|string|max:50',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'dosen',
            'kelas' => $validated['kelas'],
            'prodi' => $validated['prodi'],
        ]);

        return redirect()->route('admin.dosen.index')->with('success', 'Akun dosen berhasil ditambahkan.');
    }

    public function dosenEdit($id)
    {
        $dosen = User::where('role', 'dosen')->findOrFail($id);
        $allKelas = Mahasiswa::select('kelas')->distinct()->pluck('kelas');
        $allProdi = Mahasiswa::select('prodi')->distinct()->pluck('prodi');
        return view('admin.dosen.edit', compact('dosen', 'allKelas', 'allProdi'));
    }

    public function dosenUpdate(Request $request, $id)
    {
        $dosen = User::where('role', 'dosen')->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $dosen->id,
            'kelas' => 'required|string|max:10',
            'prodi' => 'required|string|max:50',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $validated = $request->validate($rules);

        $dosen->name = $validated['name'];
        $dosen->email = $validated['email'];
        $dosen->kelas = $validated['kelas'];
        $dosen->prodi = $validated['prodi'];

        if ($request->filled('password')) {
            $dosen->password = bcrypt($validated['password']);
        }

        $dosen->save();

        return redirect()->route('admin.dosen.index')->with('success', 'Akun dosen berhasil diperbarui.');
    }

    public function dosenDestroy($id)
    {
        $dosen = User::where('role', 'dosen')->findOrFail($id);
        $dosen->delete();

        return redirect()->route('admin.dosen.index')->with('success', 'Akun dosen berhasil dihapus.');
    }

    public function rfidEditModeStatus()
    {
        // Beri tahu backend bahwa form sedang terbuka, tahan selama 5 detik
        \Illuminate\Support\Facades\Cache::put('rfid_edit_mode', true, 5);

        // Ambil uid yang baru saja di-scan (jika ada)
        $lastScannedUid = \Illuminate\Support\Facades\Cache::pull('last_scanned_uid');

        return response()->json([
            'status' => 'success',
            'uid' => $lastScannedUid
        ]);
    }

    public function toggleDeviceStatus(Request $request)
    {
        $status = $request->input('status'); // 'on' or 'off'
        \Illuminate\Support\Facades\Cache::forever('device_status', $status);
        
        return response()->json([
            'status' => 'success',
            'device_status' => $status
        ]);
    }
}

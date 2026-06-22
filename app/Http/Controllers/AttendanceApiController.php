<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceApiController extends Controller
{
    const JAM_MASUK = '07:30:00';

    public function store(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:36|regex:/^[A-F0-9]+$/',
        ]);

        $uid = $request->uid;
        $waktuSekarang = Carbon::now();

        $mahasiswa = Mahasiswa::where('uid_ktm', $uid)->first();

        // Mode edit form (untuk scan UID saat admin edit data)
        if (Cache::has('rfid_edit_mode')) {
            Cache::put('last_scanned_uid', $uid, 10);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'UID ditangkap untuk mode edit'
            ], 200);
        }

        $isNewUser = false;
        // Logika baru: Jika UID tidak terdaftar, simpan sebagai data mahasiswa baru
        if (!$mahasiswa) {
            $isNewUser = true;
            $mahasiswa = new Mahasiswa();
            $mahasiswa->uid_ktm = $uid;
            $mahasiswa->nim     = 'NEW-' . $uid;
            $mahasiswa->nama    = 'User ' . $uid;
            $mahasiswa->kelas   = '-';
            $mahasiswa->prodi   = '-';
            $mahasiswa->save();
        }

        // Cek sudah absen hari ini
        $hariIni = Carbon::today();
        $absensiHariIni = Absensi::where('uid_ktm', $uid)
            ->whereDate('waktu_masuk', $hariIni)
            ->first();

        if ($absensiHariIni) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Sudah Absen Hari Ini'
            ], 400);
        }

        // ✅ Hitung status kehadiran & keterlambatan
        $jamMasuk = Carbon::createFromTimeString(self::JAM_MASUK);
        $keterlambatanMenit = null;
        
        if ($waktuSekarang->gt($jamMasuk)) {
            $statusKehadiran = 'Terlambat';
            // Hitung selisih menit (dibulatkan ke atas)
            $keterlambatanMenit = (int) abs($waktuSekarang->diffInMinutes($jamMasuk));
        } else {
            $statusKehadiran = 'Hadir';
        }

        // Simpan absensi
        $absensi = Absensi::create([
            'uid_ktm'              => $uid,
            'waktu_masuk'          => $waktuSekarang,
            'status'               => $statusKehadiran,
            'keterlambatan_menit'  => $keterlambatanMenit,
        ]);

        // ✅ Pesan response yang informatif
        $messageTampil = $statusKehadiran === 'Terlambat'
            ? "Terlambat {$keterlambatanMenit} menit"
            : 'Hadir';

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Absen Berhasil',
            'data'    => [
                'nama'                 => $mahasiswa->nama,
                'status'               => $statusKehadiran,
                'keterlambatan_menit'  => $keterlambatanMenit,
                'status_label'         => $messageTampil,
                'waktu'                => $waktuSekarang->format('H:i:s'),
                'is_new_user'          => $isNewUser,
            ]
        ], 200);
    }

    public function updateDeviceStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:on,off'
        ]);

        Cache::forever('device_status', $request->status);

        return response()->json([
            'status' => 'success',
            'message' => 'Status perangkat berhasil diupdate'
        ]);
    }

    public function getDeviceStatus()
    {
        $status = Cache::get('device_status', 'on');
        return response()->json([
            'status' => $status
        ]);
    }
}

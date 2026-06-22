<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:36|regex:/^[A-F0-9]+$/',
        ]);

        $uid = $request->uid;
        $waktuSekarang = Carbon::now(); 

        $mahasiswa = Mahasiswa::where('uid_ktm', $uid)->first();

        // Cek apakah sedang dalam mode edit form
        if (Cache::has('rfid_edit_mode')) {
            Cache::put('last_scanned_uid', $uid, 10);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'UID ditangkap untuk mode edit'
            ], 200);
        }

        // Logika baru: Jika UID tidak terdaftar, kembalikan response belum terdaftar
        if (!$mahasiswa) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Belum Terdaftar'
            ], 404);
        }

        $hariIni = Carbon::today();
        $sudahAbsen = Absensi::where('uid_ktm', $uid)
                            ->whereDate('waktu_masuk', $hariIni)
                            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Sudah Absen Hari Ini'
            ], 400);
        }

        $jamMasuk = Carbon::createFromTimeString('08:00:00');
        
        if ($waktuSekarang->toTimeString() > $jamMasuk->toTimeString()) {
            $statusKehadiran = 'Terlambat';
        } else {
            $statusKehadiran = 'Hadir';
        }

        $absensi = new Absensi();
        $absensi->uid_ktm     = $uid;
        $absensi->waktu_masuk = $waktuSekarang;
        $absensi->status      = $statusKehadiran;
        $absensi->save();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Absen Berhasil',
            'data'    => [
                'nama'   => $mahasiswa->nama,
                'status' => $statusKehadiran,
                'waktu'  => $waktuSekarang->format('H:i:s')
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

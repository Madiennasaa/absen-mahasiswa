<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;

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

        // Logika baru: Jika UID tidak ada, simpan sebagai data baru
        if (!$mahasiswa) {
            $mahasiswa = new Mahasiswa();
            $mahasiswa->uid_ktm = $uid;
            $mahasiswa->nim     = '-';
            $mahasiswa->nama    = 'User ' . $uid;
            $mahasiswa->kelas   = '-';
            $mahasiswa->prodi   = '-';
            $mahasiswa->save();

            return response()->json([
                'status'  => 'success',
                'code'    => 201,
                'message' => 'UID Baru Disimpan'
            ], 201);
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
}
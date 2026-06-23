<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;

class TandaiAlfa extends Command
{
    protected $signature = 'absensi:tandai-alfa';
    protected $description = 'Tandai mahasiswa yang belum absen hari ini sebagai Alfa';

    public function handle()
    {
        $hariIni = Carbon::today();
        
        // Ambil semua UID yang sudah absen hari ini
        $sudahAbsen = Absensi::whereDate('waktu_masuk', $hariIni)
            ->pluck('uid_ktm')
            ->toArray();

        // Mahasiswa yang belum absen = Alfa
        $belumAbsen = Mahasiswa::whereNotIn('uid_ktm', $sudahAbsen)->get();

        foreach ($belumAbsen as $mhs) {
            Absensi::create([
                'uid_ktm'             => $mhs->uid_ktm,
                'waktu_masuk'         => $hariIni->setTime(23, 59, 59),
                'status'              => 'Alfa',
                'keterlambatan_menit' => null,
            ]);
        }

        $this->info("Berhasil menandai {$belumAbsen->count()} mahasiswa sebagai Alfa.");
    }
}
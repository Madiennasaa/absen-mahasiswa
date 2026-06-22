<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Mahasiswa
        $mahasiswaData = [
            // Kelas 3A - Teknik Informatika (Scoped to lecturer dosen@gmail.com)
            ['uid_ktm' => 'A1B2C3D4', 'nim' => '2105101001', 'nama' => 'Budi Santoso', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'I9J0K1L2', 'nim' => '2105101002', 'nama' => 'Andi Wijaya', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'K1L2M3N4', 'nim' => '2105101003', 'nama' => 'Aditya Pratama', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'O5P6Q7R8', 'nim' => '2105101004', 'nama' => 'Citra Lestari', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'S9T0U1V2', 'nim' => '2105101005', 'nama' => 'Dian Kusuma', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'W3X4Y5Z6', 'nim' => '2105101006', 'nama' => 'Eko Prasetyo', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'A7B8C9D0', 'nim' => '2105101007', 'nama' => 'Fajar Nugroho', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'E1F2G3H4', 'nim' => '2105101008', 'nama' => 'Gita Permata', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'I5J6K7L8', 'nim' => '2105101009', 'nama' => 'Hendra Wijaya', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],
            ['uid_ktm' => 'M9N0O1P2', 'nim' => '2105101010', 'nama' => 'Indah Cahyani', 'kelas' => '3A', 'prodi' => 'Teknik Informatika'],

            // Kelas 3B - Sistem Informasi
            ['uid_ktm' => 'E5F6G7H8', 'nim' => '2105201001', 'nama' => 'Siti Rahayu', 'kelas' => '3B', 'prodi' => 'Sistem Informasi'],
            ['uid_ktm' => 'Q7R8S9T0', 'nim' => '2105201002', 'nama' => 'Joko Prasetyo', 'kelas' => '3B', 'prodi' => 'Sistem Informasi'],
            
            // Kelas 3C - Manajemen Informatika
            ['uid_ktm' => 'M3N4O5P6', 'nim' => '2105301001', 'nama' => 'Rina Dewi', 'kelas' => '3C', 'prodi' => 'Manajemen Informatika'],
        ];

        // Clean existing to avoid duplicate key issues on KTM/NIM
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Absensi::truncate();
        Mahasiswa::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        foreach ($mahasiswaData as $data) {
            Mahasiswa::create($data);
        }

        // 2. Seed Absensi for the past 14 days
        $mhsList = Mahasiswa::all();
        $startDate = Carbon::today()->subDays(14);

        for ($i = 0; $i < 14; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            
            foreach ($mhsList as $mhs) {
                // Random status: Hadir (70%), Terlambat (20%), Alpa (10% - meaning no record)
                $rand = rand(1, 100);
                
                if ($rand <= 70) {
                    // Hadir (early morning between 07:00 and 07:29)
                    $hour = 7;
                    $minute = rand(0, 29);
                    $waktuMasuk = $currentDate->copy()->setTime($hour, $minute, rand(0, 59));
                    
                    Absensi::create([
                        'uid_ktm' => $mhs->uid_ktm,
                        'waktu_masuk' => $waktuMasuk,
                        'status' => 'Hadir',
                        'keterlambatan_menit' => null,
                    ]);
                } elseif ($rand <= 90) {
                    // Terlambat (between 07:31 and 08:30)
                    $hour = 7;
                    $minute = rand(31, 59);
                    $keterlambatan = $minute - 30; // Assuming limit is 07:30
                    if (rand(1, 2) === 1) {
                        $hour = 8;
                        $minute = rand(0, 30);
                        $keterlambatan = 30 + $minute;
                    }
                    
                    $waktuMasuk = $currentDate->copy()->setTime($hour, $minute, rand(0, 59));
                    
                    Absensi::create([
                        'uid_ktm' => $mhs->uid_ktm,
                        'waktu_masuk' => $waktuMasuk,
                        'status' => 'Terlambat',
                        'keterlambatan_menit' => $keterlambatan,
                    ]);
                }
                // If rand > 90, it is Alpa (no record inserted)
            }
        }
    }
}

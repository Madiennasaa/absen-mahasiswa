<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Mahasiswa::insert([
            [
                'uid_ktm' => 'A1B2C3D4',
                'nim' => '1234567890',
                'nama' => 'Budi Santoso',
                'kelas' => '3A',
                'prodi' => 'Teknik Informatika',
            ],
            [
                'uid_ktm' => 'E5F6G7H8',
                'nim' => '0987654321',
                'nama' => 'Siti Rahayu',
                'kelas' => '3B',
                'prodi' => 'Sistem Informasi',
            ],
            [
                'uid_ktm' => 'I9J0K1L2',
                'nim' => '1122334455',
                'nama' => 'Andi Wijaya',
                'kelas' => '3A',
                'prodi' => 'Teknik Informatika',
            ],
            [
                'uid_ktm' => 'M3N4O5P6',
                'nim' => '5566778899',
                'nama' => 'Rina Dewi',
                'kelas' => '3C',
                'prodi' => 'Manajemen Informatika',
            ],
            [
                'uid_ktm' => 'Q7R8S9T0',
                'nim' => '6677889900',
                'nama' => 'Joko Prasetyo',
                'kelas' => '3B',
                'prodi' => 'Sistem Informasi',
            ],
        ]);
    }
}

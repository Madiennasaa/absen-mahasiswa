<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Create Dosen User
        User::create([
            'name' => 'Dosen Pengampu',
            'email' => 'dosen@gmail.com',
            'password' => bcrypt('dosen123'),
            'role' => 'dosen',
            'kelas' => '3A',
            'prodi' => 'Teknik Informatika',
        ]);

        // Call Mahasiswa Seeder
        $this->call(MahasiswaSeeder::class);
    }
}

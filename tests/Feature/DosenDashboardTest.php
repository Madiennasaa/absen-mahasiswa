<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;

class DosenDashboardTest extends TestCase
{
    use RefreshDatabase;

    private $dosen;
    private $mhsActive;
    private $mhsOther;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a Dosen user scoped to '3A' and 'Teknik Informatika'
        $this->dosen = User::create([
            'name' => 'Dosen 3A',
            'email' => 'dosen3a@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'kelas' => '3A',
            'prodi' => 'Teknik Informatika',
        ]);

        // Create a Mahasiswa in the Dosen's scope
        $this->mhsActive = Mahasiswa::create([
            'uid_ktm' => 'A1B2C3D4',
            'nim' => '1234567890',
            'nama' => 'Mahasiswa Scope A',
            'kelas' => '3A',
            'prodi' => 'Teknik Informatika',
        ]);

        // Create a Mahasiswa outside the Dosen's scope
        $this->mhsOther = Mahasiswa::create([
            'uid_ktm' => 'E5F6G7H8',
            'nim' => '0987654321',
            'nama' => 'Mahasiswa Other B',
            'kelas' => '3B',
            'prodi' => 'Sistem Informasi',
        ]);
    }

    public function test_dosen_dashboard_requires_authentication()
    {
        $response = $this->get('/dosen');
        $response->assertRedirect('/login');
    }

    public function test_dosen_dashboard_requires_dosen_role()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/dosen');
        $response->assertStatus(403); // assuming middleware aborts with 403
    }

    public function test_dosen_dashboard_shows_only_scoped_mahasiswa()
    {
        // Mock time to today
        Carbon::setTestNow(Carbon::today()->setTime(8, 0, 0));

        // Create absensi for both
        Absensi::create([
            'uid_ktm' => $this->mhsActive->uid_ktm,
            'waktu_masuk' => Carbon::now(),
            'status' => 'Hadir',
        ]);

        Absensi::create([
            'uid_ktm' => $this->mhsOther->uid_ktm,
            'waktu_masuk' => Carbon::now(),
            'status' => 'Hadir',
        ]);

        $response = $this->actingAs($this->dosen)->get('/dosen');

        $response->assertStatus(200);
        $response->assertSee($this->mhsActive->nama);
        $response->assertSee($this->mhsActive->nim);
        $response->assertDontSee($this->mhsOther->nama);
        $response->assertDontSee($this->mhsOther->nim);

        Carbon::setTestNow();
    }

    public function test_dosen_export_pdf_is_scoped()
    {
        $response = $this->actingAs($this->dosen)->get('/dosen/export/pdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_dosen_export_excel_is_scoped()
    {
        $response = $this->actingAs($this->dosen)->get('/dosen/export/excel');
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
    }

    public function test_dosen_live_monitor_is_accessible()
    {
        $response = $this->actingAs($this->dosen)->get('/dosen/live');
        $response->assertStatus(200);
    }

    public function test_dosen_live_data_is_scoped()
    {
        Carbon::setTestNow(Carbon::today()->setTime(8, 0, 0));

        Absensi::create([
            'uid_ktm' => $this->mhsActive->uid_ktm,
            'waktu_masuk' => Carbon::now(),
            'status' => 'Hadir',
        ]);

        Absensi::create([
            'uid_ktm' => $this->mhsOther->uid_ktm,
            'waktu_masuk' => Carbon::now(),
            'status' => 'Hadir',
        ]);

        $response = $this->actingAs($this->dosen)->get('/dosen/live-data');
        $response->assertStatus(200);

        $response->assertJsonFragment(['nama' => $this->mhsActive->nama]);
        $response->assertJsonMissing(['nama' => $this->mhsOther->nama]);

        Carbon::setTestNow();
    }
}

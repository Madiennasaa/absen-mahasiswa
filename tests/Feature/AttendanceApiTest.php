<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    private $deviceKey = 'my_secure_device_key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.device_key' => $this->deviceKey]);
    }

    public function test_tap_rfid_requires_valid_device_key()
    {
        $response = $this->postJson('/api/tap-rfid', ['uid' => 'A1B2C3D4']);
        $response->assertStatus(401)
                 ->assertJsonFragment(['message' => 'Unauthorized device access']);

        $response = $this->postJson('/api/tap-rfid', ['uid' => 'A1B2C3D4'], [
            'X-Device-Key' => 'wrong_key'
        ]);
        $response->assertStatus(401);
    }

    public function test_tap_rfid_fails_when_uid_not_registered()
    {
        $response = $this->postJson('/api/tap-rfid', ['uid' => 'ABCDEF12'], [
            'X-Device-Key' => $this->deviceKey
        ]);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => 'error',
                     'code' => 404,
                     'message' => 'UID Tidak Dikenali!'
                 ]);
    }

    public function test_tap_rfid_records_attendance_as_hadir_before_8am()
    {
        $mahasiswa = Mahasiswa::create([
            'uid_ktm' => 'A1B2C3D4',
            'nim' => '1234567890',
            'nama' => 'Budi Santoso',
            'kelas' => '3A',
            'prodi' => 'Teknik Informatika',
        ]);

        // Mock time to 07:30:00
        Carbon::setTestNow(Carbon::today()->setTime(7, 30, 0));

        $response = $this->postJson('/api/tap-rfid', ['uid' => 'A1B2C3D4'], [
            'X-Device-Key' => $this->deviceKey
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'code' => 200,
                     'message' => 'Absensi Berhasil',
                     'data' => [
                         'nama' => 'Budi Santoso',
                         'status' => 'Hadir',
                         'waktu' => '07:30:00'
                     ]
                 ]);

        $this->assertDatabaseHas('absensi', [
            'uid_ktm' => 'A1B2C3D4',
            'status' => 'Hadir'
        ]);

        Carbon::setTestNow(); // Reset mock time
    }

    public function test_tap_rfid_records_attendance_as_terlambat_after_8am()
    {
        $mahasiswa = Mahasiswa::create([
            'uid_ktm' => 'A1B2C3D4',
            'nim' => '1234567890',
            'nama' => 'Budi Santoso',
            'kelas' => '3A',
            'prodi' => 'Teknik Informatika',
        ]);

        // Mock time to 08:05:00
        Carbon::setTestNow(Carbon::today()->setTime(8, 5, 0));

        $response = $this->postJson('/api/tap-rfid', ['uid' => 'A1B2C3D4'], [
            'X-Device-Key' => $this->deviceKey
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'code' => 200,
                     'message' => 'Absensi Berhasil',
                     'data' => [
                         'nama' => 'Budi Santoso',
                         'status' => 'Terlambat',
                         'waktu' => '08:05:00'
                     ]
                 ]);

        $this->assertDatabaseHas('absensi', [
            'uid_ktm' => 'A1B2C3D4',
            'status' => 'Terlambat'
        ]);

        Carbon::setTestNow(); // Reset mock time
    }

    public function test_tap_rfid_fails_when_already_checked_in_today()
    {
        $mahasiswa = Mahasiswa::create([
            'uid_ktm' => 'A1B2C3D4',
            'nim' => '1234567890',
            'nama' => 'Budi Santoso',
            'kelas' => '3A',
            'prodi' => 'Teknik Informatika',
        ]);

        Absensi::create([
            'uid_ktm' => 'A1B2C3D4',
            'waktu_masuk' => Carbon::today()->setTime(7, 30, 0),
            'status' => 'Hadir'
        ]);

        $response = $this->postJson('/api/tap-rfid', ['uid' => 'A1B2C3D4'], [
            'X-Device-Key' => $this->deviceKey
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'status' => 'error',
                     'code' => 400,
                     'message' => 'Anda Sudah Absen Hari Ini!'
                 ]);
    }
}

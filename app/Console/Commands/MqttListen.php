<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT topics and save to database';

    public function handle()
    {
        $server   = 'fef9f2859a7244f0b347d8aef97a6df7.s1.eu.hivemq.cloud';
        $port     = 8883;
        $clientId = 'Laravel-Listener-' . uniqid();
        $username = 'uas.kelompok1';
        $password = 'Admin123';

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password)
            ->setUseTls(true)
            ->setTlsVerifyPeer(false)
            ->setKeepAliveInterval(60);

        $mqtt = new MqttClient($server, $port, $clientId);

        try {
            $mqtt->connect($connectionSettings, true);
            $this->info("Connected to HiveMQ Cloud. Listening for RFID taps...");

            $mqtt->subscribe('kampus/rfid/tap', function ($topic, $message) {
                $uid = trim($message);
                $this->info("Received RFID Tap: " . $uid);
                $this->processRfidTap($uid);
            }, 0);

            // Keep listening
            $mqtt->loop(true);
            $mqtt->disconnect();
        } catch (\Exception $e) {
            $this->error("MQTT Error: " . $e->getMessage());
        }
    }

    private function processRfidTap($uid)
    {
        $waktuSekarang = Carbon::now();
        $mahasiswa = Mahasiswa::where('uid_ktm', $uid)->first();

        // 1. Cek mode edit form
        if (Cache::has('rfid_edit_mode')) {
            Cache::put('last_scanned_uid', $uid, 10);
            $this->info("UID $uid ditangkap untuk form edit.");
            return;
        }

        // 2. Jika Mahasiswa belum ada, buat baru
        if (!$mahasiswa) {
            $mahasiswa = new Mahasiswa();
            $mahasiswa->uid_ktm = $uid;
            $mahasiswa->nim     = 'NEW-' . $uid;
            $mahasiswa->nama    = 'User ' . $uid;
            $mahasiswa->kelas   = '-';
            $mahasiswa->prodi   = '-';
            $mahasiswa->save();
            Log::info("Data mahasiswa baru dibuat: User {$uid}");
        }

        // 3. Catat Absensi
        $hariIni = Carbon::today();
        $sudahAbsen = Absensi::where('uid_ktm', $uid)
                             ->whereDate('waktu_masuk', $hariIni)
                             ->exists();

        if ($sudahAbsen) {
            $this->info("UID $uid sudah absen hari ini.");
            return;
        }

        $jamMasuk = Carbon::createFromTimeString('08:00:00');
        $statusKehadiran = ($waktuSekarang->toTimeString() > $jamMasuk->toTimeString()) ? 'Terlambat' : 'Hadir';

        $absensi = new Absensi();
        $absensi->uid_ktm     = $uid;
        $absensi->waktu_masuk = $waktuSekarang;
        $absensi->status      = $statusKehadiran;
        $absensi->save();

        $this->info("Absensi dicatat untuk $uid ($statusKehadiran).");
    }
}

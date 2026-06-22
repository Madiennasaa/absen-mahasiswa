<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Mahasiswa:\n";
foreach (\App\Models\Mahasiswa::all() as $m) {
    echo $m->uid_ktm . " - " . $m->nama . "\n";
}
echo "\nAbsensi:\n";
foreach (\App\Models\Absensi::all() as $a) {
    echo $a->uid_ktm . " - " . $a->waktu_masuk . " - " . $a->status . "\n";
}
echo "\nCache rfid_edit_mode:\n";
echo \Illuminate\Support\Facades\Cache::get('rfid_edit_mode') ? 'TRUE' : 'FALSE';
echo "\n";

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Tambah kolom menit telat, null jika Hadir atau Alfa
            $table->unsignedSmallInteger('keterlambatan_menit')->nullable()->after('status');
        });

        // Update enum status agar support 'Alfa'
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('Hadir', 'Terlambat', 'Alfa') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn('keterlambatan_menit');
        });
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('Hadir', 'Terlambat') NOT NULL");
    }
};
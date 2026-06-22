<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration fix:
 * 1. Tambah kolom keterlambatan_menit jika belum ada
 * 2. Fix typo enum: 'Alfa' → 'Alpa'
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom keterlambatan_menit jika belum ada
        if (!Schema::hasColumn('absensi', 'keterlambatan_menit')) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->unsignedSmallInteger('keterlambatan_menit')->nullable()->after('status');
            });
        }

        // Fix enum: Alfa → Alpa (sesuai yang dipakai di controller & blade)
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('Hadir', 'Terlambat', 'Alpa') NOT NULL DEFAULT 'Hadir'");

        // Kalau ada data lama yang tersimpan sebagai 'Alfa', update ke 'Alpa'
        DB::statement("UPDATE absensi SET status = 'Alpa' WHERE status = 'Alfa'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('Hadir', 'Terlambat') NOT NULL");

        Schema::table('absensi', function (Blueprint $table) {
            if (Schema::hasColumn('absensi', 'keterlambatan_menit')) {
                $table->dropColumn('keterlambatan_menit');
            }
        });
    }
};
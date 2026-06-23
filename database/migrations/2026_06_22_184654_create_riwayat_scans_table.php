<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riwayat_scans', function (Blueprint $table) {
            $table->id();
            $table->string('uid_ktm');
            $table->string('nim')->nullable();
            $table->string('nama')->nullable();
            $table->string('kelas')->nullable();
            $table->string('prodi')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_scans');
    }
};

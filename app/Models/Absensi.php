<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'uid_ktm',
        'waktu_masuk',
        'status',
        'keterlambatan_menit',  // ← tambah ini
    ];

    protected $casts = [
        'waktu_masuk' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'uid_ktm', 'uid_ktm');
    }

    // Helper: label status + menit terlambat untuk ditampilkan
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'Terlambat' && $this->keterlambatan_menit) {
            return "Terlambat {$this->keterlambatan_menit} menit";
        }
        return $this->status;
    }
}
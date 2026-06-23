<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid_ktm',
        'nim',
        'nama',
        'kelas',
        'prodi',
        'status'
    ];
}

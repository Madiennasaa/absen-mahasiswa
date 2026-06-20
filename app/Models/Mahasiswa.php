<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';
    protected $primaryKey = 'uid_ktm';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uid_ktm',
        'nim',
        'nama',
        'kelas',
        'prodi',
    ];

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'uid_ktm', 'uid_ktm');
    }
}
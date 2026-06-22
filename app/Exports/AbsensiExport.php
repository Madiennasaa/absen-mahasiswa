<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function($item) {
            return [
                'Tanggal' => $item['tanggal'],
                'NIM' => $item['nim'],
                'Nama' => $item['nama'],
                'Kelas' => $item['kelas'],
                'Prodi' => $item['prodi'],
                'Waktu Masuk' => $item['waktu'],
                'Keterlambatan (Menit)' => ($item['status'] === 'Terlambat' && $item['keterlambatan_menit'] !== null) ? $item['keterlambatan_menit'] : '-',
                'Status' => $item['status'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIM',
            'Nama',
            'Kelas',
            'Prodi',
            'Waktu Masuk',
            'Keterlambatan (Menit)',
            'Status'
        ];
    }
}

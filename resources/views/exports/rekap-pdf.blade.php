<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Absensi Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 3px 0;
        }
        .info-table td.label {
            width: 150px;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .status-hadir {
            color: #10b981;
            font-weight: bold;
        }
        .status-terlambat {
            color: #f59e0b;
            font-weight: bold;
        }
        .status-alpa {
            color: #f43f5e;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Rekap Absensi Mahasiswa</h2>
        <p>Sistem Absensi berbasis IoT (RFID + ESP32)</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Periode Laporan</td>
            <td>: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Cetak</td>
            <td>: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 15%">NIM</th>
                <th>Nama</th>
                <th style="width: 10%">Kelas</th>
                <th style="width: 15%">Waktu Masuk</th>
                <th style="width: 15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapData as $item)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item['tanggal'] }}</td>
                    <td>{{ $item['nim'] }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ $item['kelas'] }}</td>
                    <td>{{ $item['waktu'] }}</td>
                    <td>
                        @if($item['status'] === 'Hadir')
                            <span class="status-hadir">Hadir</span>
                        @elseif($item['status'] === 'Terlambat')
                            <span class="status-terlambat">Terlambat</span>
                        @else
                            <span class="status-alpa">Alpa</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data absensi untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh Sistem Absensi Mahasiswa RFID.
    </div>

</body>
</html>

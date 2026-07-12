<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h2 {
            margin: 0 0 6px;
            text-align: center;
            font-size: 17px;
        }

        .meta {
            margin: 0 0 12px;
            text-align: center;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #555;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #e5eefb;
            text-transform: uppercase;
            font-size: 9px;
        }

        .status {
            font-weight: bold;
            text-transform: uppercase;
        }

        .hadir {
            color: #067647;
        }

        .terlambat {
            color: #b45309;
        }

        .alpa {
            color: #b42318;
        }

        .absent-row {
            background: #fff1f1;
        }
    </style>
</head>
<body>
    <h2>REKAPITULASI ABSENSI SISWA<br>SMA MUHAMMADIYAH 2 KOTA TANGERANG</h2>
    <p class="meta">
        Dicetak: {{ now()->format('d-m-Y H:i') }}
        @if (($filters['from'] ?? null) || ($filters['to'] ?? null))
            | Periode: {{ $filters['from'] ?? '-' }} s/d {{ $filters['to'] ?? '-' }}
        @endif
    </p>

    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Tanggal Sesi</th>
            <th>Jam Sesi</th>
            <th>Waktu Scan</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Mata Pelajaran</th>
            <th>Guru</th>
            <th>Status</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($items as $row)
            <tr class="{{ $row->is_absent ? 'absent-row' : '' }}">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->tanggal }}</td>
                <td>{{ $row->jam_sesi }}</td>
                <td>{{ $row->waktu_scan }}</td>
                <td>{{ $row->nis }}</td>
                <td>{{ $row->nama }}</td>
                <td>{{ $row->kelas }}</td>
                <td>{{ $row->mapel }}</td>
                <td>{{ $row->guru }}</td>
                <td class="status {{ $row->status }}">{{ $row->status_label }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10">Tidak ada data sesi absensi pada filter ini.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>

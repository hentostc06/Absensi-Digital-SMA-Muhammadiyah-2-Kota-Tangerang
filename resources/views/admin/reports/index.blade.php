@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
<div class="page-head">
    <div>
        <h1>Laporan Absensi</h1>
        <p>Filter, unduh PDF, dan ekspor Excel. Siswa yang belum scan tetap ditampilkan sebagai Alpa.</p>
    </div>
</div>

<form class="card filters report-filter-card" method="get">
    <label>
        Dari
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
    </label>

    <label>
        Sampai
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
    </label>

    <label>
        Kelas
        <select name="class_id">
            <option value="">Semua</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(($filters['class_id'] ?? '') == $class->id)>
                    {{ $class->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label>
        Mapel
        <select name="subject_id">
            <option value="">Semua</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(($filters['subject_id'] ?? '') == $subject->id)>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label>
        Status
        <select name="status">
            <option value="">Semua</option>
            @foreach (['hadir', 'terlambat', 'alpa'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </label>

    <button class="btn primary" type="submit">Terapkan</button>
    <a class="btn" href="{{ route('admin.reports.pdf', request()->query()) }}">PDF</a>
    <a class="btn" href="{{ route('admin.reports.excel', request()->query()) }}">Excel</a>
</form>

<div class="report-summary-line">
    <span>Total data: <b>{{ $summary['total'] ?? $items->total() }}</b></span>
    <span>Hadir: <b>{{ $summary['hadir'] ?? 0 }}</b></span>
    <span>Terlambat: <b>{{ $summary['terlambat'] ?? 0 }}</b></span>
    <span>Alpa: <b>{{ $summary['alpa'] ?? 0 }}</b></span>
</div>

<div class="card table-wrap">
    <table>
        <thead>
        <tr>
            <th>Tanggal Sesi</th>
            <th>Waktu Scan</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Mapel</th>
            <th>Guru</th>
            <th>Status</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($items as $row)
            @php
                $badgeClass = match ($row->status) {
                    'hadir' => 'green',
                    'terlambat' => 'yellow',
                    default => 'red',
                };
            @endphp

            <tr class="{{ $row->is_absent ? 'report-row-absent' : '' }}">
                <td>
                    <strong>{{ $row->tanggal }}</strong>
                    <small>{{ $row->jam_sesi }}</small>
                </td>
                <td>{{ $row->waktu_scan }}</td>
                <td><strong>{{ $row->nis }}</strong></td>
                <td>{{ $row->nama }}</td>
                <td>{{ $row->kelas }}</td>
                <td>{{ $row->mapel }}</td>
                <td>{{ $row->guru }}</td>
                <td>
                    <span class="badge {{ $badgeClass }}">
                        {{ $row->status_label }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="empty">Tidak ada data sesi absensi pada filter ini.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{ $items->links('vendor.pagination.badcoding') }}
@endsection

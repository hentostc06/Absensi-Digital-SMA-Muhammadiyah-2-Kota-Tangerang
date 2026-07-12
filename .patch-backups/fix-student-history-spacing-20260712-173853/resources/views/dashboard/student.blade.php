@extends('layouts.app')
@section('title', 'Dashboard Siswa')
@section('content')
<div class="hero-dashboard student-hero">
    <div>
        <span class="section-kicker">DASHBOARD SISWA</span>
        <h1>Halo, {{ auth()->user()->name }}</h1>
        <p>{{ $student?->schoolClass?->name ?? 'Kelas belum diatur' }} — lakukan absensi melalui QR Code dari guru.</p>
    </div>
    <a href="{{ route('student.scan') }}" class="btn primary">Scan QR Sekarang</a>
</div>

<div class="dashboard-grid">
    <section class="card glass-panel">
        <h3>Absensi Hari Ini</h3>
        <div class="timeline-list">
            @forelse ($todayAttendance as $attendance)
                <div class="timeline-item">
                    <div class="timeline-avatar">✓</div>
                    <div><strong>{{ $attendance->session->subject->name }}</strong><span>{{ ucfirst($attendance->status) }} • {{ optional($attendance->scanned_at)->format('H:i') }}</span></div>
                </div>
            @empty
                <p class="muted">Belum ada absensi hari ini.</p>
            @endforelse
        </div>
    </section>

    <section class="card glass-panel">
        <h3>Riwayat Terbaru</h3>
        <div class="timeline-list">
            @forelse ($recent as $attendance)
                <div class="timeline-item">
                    <div class="timeline-avatar">{{ strtoupper(substr($attendance->status, 0, 1)) }}</div>
                    <div><strong>{{ $attendance->session->subject->name }}</strong><span>{{ ucfirst($attendance->status) }} • {{ optional($attendance->scanned_at)->format('d/m/Y H:i') }}</span></div>
                </div>
            @empty
                <p class="muted">Belum ada riwayat.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection

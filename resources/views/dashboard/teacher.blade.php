@extends('layouts.app')
@section('title', 'Dashboard Guru')
@section('content')
<div class="hero-dashboard teacher-hero">
    <div>
        <span class="section-kicker">DASHBOARD GURU</span>
        <h1>Jadwal Mengajar Hari Ini</h1>
        <p>Sistem otomatis menampilkan jadwal aktif dan sesi absensi yang sedang berjalan.</p>
    </div>
    <a href="{{ route('teacher.sessions.index') }}" class="btn primary">Buka Sesi QR</a>
</div>

@if ($openSession)
    <div class="alert info smart-alert">
        <strong>Sesi sedang aktif:</strong>
        {{ $openSession->subject->name }} — {{ $openSession->schoolClass->name }}
        <a href="{{ route('teacher.sessions.show', $openSession) }}">Lihat QR</a>
    </div>
@endif

<div class="dashboard-grid">
    <section class="card glass-panel current-class-card">
        <span class="section-kicker">JADWAL SAAT INI</span>
        @if ($currentSchedule)
            <h2>{{ $currentSchedule->subject->name }}</h2>
            <p>{{ $currentSchedule->schoolClass->name }} • Ruang {{ $currentSchedule->room ?: '-' }}</p>
            <div class="time-pill">{{ substr($currentSchedule->start_time, 0, 5) }} - {{ substr($currentSchedule->end_time, 0, 5) }}</div>
            <form method="POST" action="{{ route('teacher.sessions.store') }}">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $currentSchedule->id }}">
                <button class="btn primary wide">Buka Absensi Sekarang</button>
            </form>
        @else
            <h2>Tidak ada jadwal berjalan</h2>
            <p>Gunakan daftar jadwal hari ini untuk membuka sesi sesuai kebutuhan.</p>
        @endif
    </section>

    <section class="card glass-panel">
        <span class="section-kicker">{{ $day }}</span>
        <h3>Daftar Jadwal Hari Ini</h3>
        <div class="schedule-list">
            @forelse ($todaySchedules as $schedule)
                <div class="schedule-item">
                    <div><strong>{{ $schedule->subject->name }}</strong><span>{{ $schedule->schoolClass->name }} • {{ $schedule->room ?: 'Ruang belum diatur' }}</span></div>
                    <div class="schedule-time">{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</div>
                </div>
            @empty
                <p class="muted">Belum ada jadwal untuk hari ini.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection

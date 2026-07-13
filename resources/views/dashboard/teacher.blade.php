@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<section class="teacher-welcome-card">
    <div>
        <span class="section-kicker">Dashboard Guru</span>
        <h1>Selamat datang, {{ $teacherTitle }} {{ auth()->user()->name }}</h1>
        <p>{{ $day }}, {{ now()->translatedFormat('d F Y') }}. Sistem otomatis membaca jadwal mengajar berdasarkan hari dan jam sekarang.</p>
    </div>

    @if ($openSession)
        <a href="{{ route('teacher.sessions.show', $openSession) }}" class="btn primary">Tampilkan QR Aktif</a>
    @else
        <a href="{{ route('teacher.sessions.index') }}" class="btn primary">Buka Sesi Absensi</a>
    @endif
</section>

@if (! $teacher)
    <div class="alert danger">Profil guru belum terhubung. Hubungi administrator sekolah.</div>
@endif

@if ($openSession)
    <div class="alert info">
        <strong>Sesi aktif:</strong>
        <span>{{ $openSession->subject->name }} · {{ $openSession->schoolClass->name }}</span>
        <a href="{{ route('teacher.sessions.show', $openSession) }}" class="btn sm primary">Tampilkan QR</a>
    </div>
@endif

<div class="teacher-auto-grid">
    <section class="card auto-schedule-card">
        <span class="section-kicker">{{ $scheduleStatus }}</span>

        @if ($suggestedSchedule)
            <h2>{{ $suggestedSchedule->subject->name }}</h2>
            <div class="auto-schedule-meta">
                <span>{{ substr($suggestedSchedule->start_time, 0, 5) }}–{{ substr($suggestedSchedule->end_time, 0, 5) }}</span>
                <span>{{ $suggestedSchedule->schoolClass->name }}</span>
                <span>{{ $suggestedSchedule->room ?: 'Ruang belum ditentukan' }}</span>
            </div>

            <p>
                Jadwal ini dipilih otomatis oleh sistem. Guru tidak perlu memilih jadwal lagi ketika membuka sesi absensi.
            </p>

            <form method="POST" action="{{ route('teacher.sessions.store') }}" class="auto-session-form">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $suggestedSchedule->id }}">
                <label>
                    Batas Hadir Normal
                    <input type="number" name="late_after_minutes" value="5" min="0" max="120" required>
                    <small class="teacher-late-help">Lewat dari batas ini, siswa tercatat terlambat.</small>
                </label>
                <button type="submit" class="btn primary">Buka Sesi Absensi</button>
            </form>
        @else
            <h2>Tidak ada jadwal hari ini.</h2>
            <p>Jika jadwal seharusnya ada, admin perlu mengecek menu Jadwal Pelajaran.</p>
        @endif
    </section>

    <section class="card">
        <h2 class="card-title">Jadwal {{ $day }}</h2>
        <p class="card-subtitle">Daftar jadwal mengajar hari ini.</p>

        <div class="schedule-list clean-schedule-list">
            @forelse ($todaySchedules as $schedule)
                <div class="schedule-item {{ $currentSchedule && $currentSchedule->id === $schedule->id ? 'current' : '' }}">
                    <div>
                        <strong>{{ $schedule->subject->name }}</strong>
                        <span>{{ $schedule->schoolClass->name }} · {{ $schedule->room ?: 'Ruang belum ditentukan' }}</span>
                    </div>
                    <span class="schedule-time">
                        {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}
                    </span>
                </div>
            @empty
                <p class="muted">Belum ada jadwal untuk hari ini.</p>
            @endforelse
        </div>
    </section>
</div>

<!-- TEACHER DASHBOARD CLEAN DUPLICATE BADGE START -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = Array.from(document.querySelectorAll('section, article, div')).filter(function (el) {
        return (el.textContent || '').includes('SEDANG BERLANGSUNG');
    });

    cards.forEach(function (card) {
        const seen = new Set();

        Array.from(card.querySelectorAll('span, b')).forEach(function (el) {
            const value = (el.textContent || '').replace(/\s+/g, ' ').trim();

            if (/^(X|XI|XII)\b/i.test(value)) {
                if (seen.has(value)) {
                    el.remove();
                } else {
                    seen.add(value);
                }
            }
        });
    });
});
</script>
<!-- TEACHER DASHBOARD CLEAN DUPLICATE BADGE END -->

@endsection

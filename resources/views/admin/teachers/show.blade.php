@extends('layouts.app')

@section('title', 'Detail Guru')

@section('content')
<section class="teacher-detail-hero">
    <div>
        <span class="section-kicker">DETAIL GURU</span>
        <h1>{{ $teacher->user->name }}</h1>
        <p>
            {{ $teacher->niy_nbm }} · {{ $teacher->user->username }}
            @if ($teacher->phone)
                · {{ $teacher->phone }}
            @endif
        </p>
    </div>

    <div class="teacher-detail-actions">
        <a class="btn" href="{{ route('admin.teachers.index') }}">Kembali</a>
        <a class="btn primary" href="{{ route('admin.teachers.edit', $teacher) }}">Edit Guru</a>
    </div>
</section>

<section class="teacher-detail-stats">
    <div class="teacher-stat-card">
        <span>Total Jadwal</span>
        <strong>{{ $schedules->count() }}</strong>
    </div>

    <div class="teacher-stat-card">
        <span>Mata Pelajaran</span>
        <strong>{{ $subjectCount }}</strong>
    </div>

    <div class="teacher-stat-card">
        <span>Kelas Diajar</span>
        <strong>{{ $classCount }}</strong>
    </div>

    <div class="teacher-stat-card">
        <span>Riwayat Sesi</span>
        <strong>{{ $sessionsCount }}</strong>
    </div>
</section>

<section class="teacher-detail-grid">
    <div class="teacher-detail-panel">
        <div class="panel-title-row">
            <div>
                <span class="section-kicker">RINGKASAN</span>
                <h2>Mengajar Apa Saja</h2>
            </div>
        </div>

        <div class="teacher-summary-list">
            @forelse ($schedules->groupBy('subject.name') as $subjectName => $subjectSchedules)
                <div class="teacher-summary-card">
                    <div class="summary-icon">M</div>
                    <div>
                        <strong>{{ $subjectName }}</strong>
                        <p>
                            {{ $subjectSchedules->pluck('schoolClass.name')->unique()->implode(', ') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="empty-state">Guru ini belum memiliki jadwal mengajar aktif.</div>
            @endforelse
        </div>
    </div>

    <div class="teacher-detail-panel">
        <div class="panel-title-row">
            <div>
                <span class="section-kicker">JADWAL</span>
                <h2>Jadwal Mengajar</h2>
            </div>
        </div>

        <div class="teacher-schedule-list">
            @forelse ($schedules as $schedule)
                <div class="teacher-schedule-card">
                    <div class="teacher-schedule-day">
                        {{ $schedule->day_of_week }}
                    </div>

                    <div class="teacher-schedule-main">
                        <strong>{{ $schedule->subject->name }}</strong>
                        <p>
                            {{ $schedule->schoolClass->name }}
                            @if ($schedule->room)
                                · {{ $schedule->room }}
                            @endif
                        </p>
                    </div>

                    <div class="teacher-schedule-time">
                        {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}
                    </div>
                </div>
            @empty
                <div class="empty-state">Belum ada jadwal untuk guru ini.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', 'Detail Kelas')

@section('content')
<section class="class-detail-hero">
    <div>
        <span class="section-kicker">DETAIL KELAS</span>
        <h1>{{ $class->name }}</h1>
        <p>
            Kode {{ $class->code }} · {{ $class->grade }} · Tahun ajaran {{ $class->academic_year }}
        </p>
    </div>

    <div class="class-detail-actions">
        <a class="btn" href="{{ route('admin.classes.index') }}">Kembali</a>
        <a class="btn primary" href="{{ route('admin.classes.edit', $class) }}">Edit Kelas</a>
    </div>
</section>

<section class="class-detail-stats">
    <div class="class-stat-card">
        <span>Total Siswa</span>
        <strong>{{ $students->count() }}</strong>
    </div>

    <div class="class-stat-card">
        <span>Total Jadwal</span>
        <strong>{{ $schedules->count() }}</strong>
    </div>

    <div class="class-stat-card">
        <span>Wali / Ruang</span>
        <strong>{{ $class->room ?? '-' }}</strong>
    </div>
</section>

<section class="class-detail-grid">
    <div class="class-detail-panel">
        <div class="panel-title-row">
            <div>
                <span class="section-kicker">SISWA</span>
                <h2>Daftar Siswa</h2>
            </div>
        </div>

        <div class="class-student-list">
            @forelse ($students as $student)
                <div class="class-student-card">
                    <div class="student-avatar">
                        {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                    </div>

                    <div>
                        <strong>{{ $student->user->name }}</strong>
                        <p>{{ $student->nis }} · {{ $student->gender === 'P' ? 'Perempuan' : ($student->gender === 'L' ? 'Laki-laki' : 'Gender belum diisi') }}</p>
                    </div>
                </div>
            @empty
                <div class="empty-state">Belum ada siswa di kelas ini.</div>
            @endforelse
        </div>
    </div>

    <div class="class-detail-panel">
        <div class="panel-title-row">
            <div>
                <span class="section-kicker">JADWAL</span>
                <h2>Jadwal Pelajaran</h2>
            </div>
        </div>

        <div class="class-schedule-list">
            @forelse ($schedules as $schedule)
                <div class="class-schedule-card">
                    <div class="schedule-day">
                        {{ $schedule->day_of_week }}
                    </div>

                    <div class="schedule-main">
                        <strong>{{ $schedule->subject->name }}</strong>
                        <p>
                            {{ $schedule->teacher->user->name }}
                            @if ($schedule->room)
                                · {{ $schedule->room }}
                            @endif
                        </p>
                    </div>

                    <div class="schedule-time-box">
                        {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}
                    </div>
                </div>
            @empty
                <div class="empty-state">Belum ada jadwal untuk kelas ini.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection

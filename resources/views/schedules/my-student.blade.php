@extends('layouts.app')

@section('title', 'Jadwal Saya')

@section('content')
<section class="my-schedule-hero">
    <div>
        <span class="section-kicker">JADWAL SAYA</span>
        <h1>Jadwal Pelajaran</h1>
        <p>Daftar jadwal pelajaran aktif sesuai kelas siswa.</p>
    </div>

    <div class="my-schedule-summary">
        <strong>{{ $items->count() }}</strong>
        <span>jadwal aktif</span>
    </div>
</section>

@if (! $student)
    <section class="my-schedule-empty">
        <strong>Profil siswa belum terhubung.</strong>
        <p>Akun ini belum memiliki data siswa. Admin perlu menghubungkan akun ke data siswa terlebih dahulu.</p>
    </section>
@elseif (! $student->school_class_id)
    <section class="my-schedule-empty">
        <strong>Kelas siswa belum diatur.</strong>
        <p>Admin perlu mengatur kelas siswa agar jadwal bisa ditampilkan.</p>
    </section>
@elseif ($items->isEmpty())
    <section class="my-schedule-empty">
        <strong>Belum ada jadwal aktif.</strong>
        <p>Admin belum membuat jadwal pelajaran untuk kelas {{ $student->schoolClass->name ?? 'ini' }}.</p>
    </section>
@else
    <section class="my-schedule-class-card">
        <span>Kelas</span>
        <strong>{{ $student->schoolClass->name ?? '-' }}</strong>
    </section>

    <section class="my-schedule-list">
        @foreach ($groupedSchedules as $day => $schedules)
            <article class="my-schedule-day-card {{ $day === $today ? 'today' : '' }}">
                <header>
                    <div>
                        <span>{{ $day === $today ? 'Hari ini' : 'Hari' }}</span>
                        <h2>{{ $day }}</h2>
                    </div>

                    <b>{{ $schedules->count() }} pelajaran</b>
                </header>

                <div class="my-schedule-items">
                    @foreach ($schedules as $schedule)
                        <div class="my-schedule-item">
                            <div class="my-schedule-time">
                                <strong>{{ substr((string) $schedule->start_time, 0, 5) }}</strong>
                                <span>{{ substr((string) $schedule->end_time, 0, 5) }}</span>
                            </div>

                            <div class="my-schedule-info">
                                <h3>{{ $schedule->subject->name ?? '-' }}</h3>
                                <p>{{ $schedule->teacher->user->name ?? 'Guru belum diatur' }}</p>

                                <div class="my-schedule-badges">
                                    <span>{{ $schedule->room ?: 'Ruang belum ditentukan' }}</span>
                                    <span>{{ $schedule->schoolClass->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        @endforeach
    </section>
@endif
@endsection

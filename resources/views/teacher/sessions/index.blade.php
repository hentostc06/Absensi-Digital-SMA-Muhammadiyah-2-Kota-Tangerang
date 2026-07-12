@extends('layouts.app')

@section('title', 'Sesi Absensi')

@section('content')
<section class="teacher-welcome-card compact">
    <div>
        <span class="section-kicker">Sesi QR Code</span>
        <h1>Sesi Absensi Guru</h1>
        <p>Mode otomatis tetap membaca jadwal berdasarkan hari dan jam sekarang. Untuk uji coba, gunakan Mode Testing Manual.</p>
    </div>

    @if ($openSession)
        <a href="{{ route('teacher.sessions.show', $openSession) }}" class="btn primary">Tampilkan QR Aktif</a>
    @endif
</section>

<section class="session-mode-grid">
    <div class="card auto-schedule-card session-auto-card">
        <span class="section-kicker">{{ $scheduleStatus }}</span>

        @if ($suggestedSchedule)
            <div class="auto-session-head">
                <div>
                    <h2>{{ $suggestedSchedule->subject->name }}</h2>
                    <div class="auto-schedule-meta">
                        <span>{{ $suggestedSchedule->day_of_week }}</span>
                        <span>{{ substr($suggestedSchedule->start_time, 0, 5) }}–{{ substr($suggestedSchedule->end_time, 0, 5) }}</span>
                        <span>{{ $suggestedSchedule->schoolClass->name }}</span>
                        <span>{{ $suggestedSchedule->room ?: 'Ruang belum ditentukan' }}</span>
                    </div>
                    <p>Jadwal ini dipilih otomatis oleh sistem.</p>
                </div>

                <form method="POST" action="{{ route('teacher.sessions.store') }}" class="auto-session-form inline">
                    @csrf
                    <input type="hidden" name="mode" value="auto">
                    <input type="hidden" name="schedule_id" value="{{ $suggestedSchedule->id }}">

                    <label>
                        Batas status terlambat
                        <input type="number" name="late_after_minutes" value="{{ old('late_after_minutes', 5) }}" min="0" max="120" required>
                    </label>

                    <button class="btn primary" type="submit">Buka Otomatis</button>
                
                    <label>
                        <span>Sesi Berakhir Setelah</span>
                        <input type="number" name="session_duration_minutes" value="{{ old('session_duration_minutes', 15) }}" min="5" max="120">
                    </label>

                </form>
            </div>
        @else
            <h2>Tidak ada jadwal otomatis.</h2>
            <p>Hari ini belum ada jadwal aktif yang cocok dengan waktu sekarang. Gunakan Mode Testing Manual di sebelah kanan untuk uji coba.</p>
        @endif
    </div>

    <div class="card testing-schedule-card">
        <span class="section-kicker">MODE TESTING</span>
        <h2>Buka Sesi Manual</h2>
        <p>Pilih jadwal apa saja milik guru ini. Cocok untuk testing, demo, dan sidang tanpa harus menunggu jam pelajaran asli.</p>

        <form method="POST" action="{{ route('teacher.sessions.store') }}" class="testing-session-form">
            @csrf
            <input type="hidden" name="mode" value="manual">

            <label>
                Pilih jadwal
                <select name="schedule_id" required>
                    <option value="">Pilih jadwal untuk testing</option>
                    @foreach ($allSchedules as $schedule)
                        <option value="{{ $schedule->id }}">
                            {{ $schedule->day_of_week }} · {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}
                            · {{ $schedule->subject->name }}
                            · {{ $schedule->schoolClass->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Batas status terlambat
                <input type="number" name="late_after_minutes" value="5" min="0" max="120" required>
            </label>

            <button class="btn primary" type="submit" @disabled($allSchedules->isEmpty())>
                Buka Sesi Testing
            </button>

            @if ($allSchedules->isEmpty())
                <small>Belum ada jadwal aktif untuk guru ini. Admin perlu membuat jadwal terlebih dahulu.</small>
            @endif
        </form>
    </div>
</section>

<section class="card table-wrap session-history-card">
    <div class="table-header">
        <div>
            <h2 class="card-title">Riwayat Sesi</h2>
            <p class="card-subtitle">Sesi terbaru ditampilkan paling atas.</p>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>Dibuka</th>
            <th>Kelas</th>
            <th>Mata Pelajaran</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($sessions as $session)
            <tr>
                <td>{{ $session->opened_at->format('d-m-Y H:i') }}</td>
                <td>{{ $session->schoolClass->name }}</td>
                <td><strong>{{ $session->subject->name }}</strong></td>
                <td>
                    <span class="status-chip {{ $session->isOpen() ? 'active' : 'inactive' }}">
                        {{ $session->isOpen() ? 'Aktif' : 'Ditutup' }}
                    </span>
                </td>
                <td>
                    <a class="btn sm" href="{{ route('teacher.sessions.show', $session) }}">Buka</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="empty">Belum ada sesi absensi.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</section>

<div class="admin-pagination-wrap">
    {{ $sessions->links() }}
</div>
@endsection

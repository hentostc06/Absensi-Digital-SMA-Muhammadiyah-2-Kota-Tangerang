@extends('layouts.app')

@section('title', 'Sesi Absensi')

@section('content')
<section class="teacher-welcome-card compact">
    <div>
        <span class="section-kicker">Sesi QR Code</span>
        <h1>Sesi Absensi Guru</h1>
        <p>Sistem otomatis memilih jadwal berdasarkan hari dan jam sekarang. Tidak perlu pilih jadwal secara manual.</p>
    </div>

    @if ($openSession)
        <a href="{{ route('teacher.sessions.show', $openSession) }}" class="btn primary">Tampilkan QR Aktif</a>
    @endif
</section>

<section class="card auto-schedule-card session-auto-card">
    <span class="section-kicker">{{ $scheduleStatus }}</span>

    @if ($suggestedSchedule)
        <div class="auto-session-head">
            <div>
                <h2>{{ $suggestedSchedule->subject->name }}</h2>
                <div class="auto-schedule-meta">
                    <span>{{ substr($suggestedSchedule->start_time, 0, 5) }}–{{ substr($suggestedSchedule->end_time, 0, 5) }}</span>
                    <span>{{ $suggestedSchedule->schoolClass->name }}</span>
                    <span>{{ $suggestedSchedule->room ?: 'Ruang belum ditentukan' }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('teacher.sessions.store') }}" class="auto-session-form inline">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $suggestedSchedule->id }}">
                <label>
                    Batas terlambat
                    <input type="number" name="late_after_minutes" value="{{ old('late_after_minutes', 15) }}" min="0" max="120" required>
                </label>
                <button class="btn primary" type="submit">Buka Sesi & Generate QR</button>
            </form>
        </div>
    @else
        <h2>Tidak ada jadwal otomatis.</h2>
        <p>Hari ini belum ada jadwal aktif untuk akun guru ini. Admin bisa menambahkan jadwal di menu Jadwal Pelajaran.</p>
    @endif
</section>

<section class="card table-wrap">
    <div class="table-header">
        <h2 class="card-title">Riwayat Sesi</h2>
        <p class="card-subtitle">Sesi terbaru ditampilkan paling atas.</p>
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
                <td><a class="btn sm" href="{{ route('teacher.sessions.show', $session) }}">Buka</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">Belum ada sesi absensi.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>

<div style="margin-top:1rem">{{ $sessions->links() }}</div>
@endsection

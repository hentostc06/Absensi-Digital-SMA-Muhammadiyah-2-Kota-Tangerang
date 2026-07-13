@extends('layouts.app')

@section('title', 'Sesi Absensi Guru')

@section('content')
@php
    $now = now();
    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $todayName = $days[((int) $now->format('N')) - 1] ?? 'Senin';

    $todayScheduleList = collect($todaySchedules ?? $allSchedules ?? [])
        ->filter(fn ($schedule) => (string) ($schedule->day_of_week ?? '') === $todayName)
        ->values();

    $current = $currentSchedule ?? null;

    if (! $current) {
        $current = $todayScheduleList->first(function ($schedule) use ($now) {
            $startAt = \Illuminate\Support\Carbon::parse($now->toDateString() . ' ' . substr((string) $schedule->start_time, 0, 8));
            $endAt = \Illuminate\Support\Carbon::parse($now->toDateString() . ' ' . substr((string) $schedule->end_time, 0, 8));
            return $now->betweenIncluded($startAt, $endAt);
        });
    }

    $canStart = false;
    $timeStatus = 'Tidak ada jadwal mengajar yang sedang berlangsung saat ini.';
    $buttonText = 'Belum Ada Jadwal';

    if ($current) {
        $startAt = \Illuminate\Support\Carbon::parse($now->toDateString() . ' ' . substr((string) $current->start_time, 0, 8));
        $endAt = \Illuminate\Support\Carbon::parse($now->toDateString() . ' ' . substr((string) $current->end_time, 0, 8));

        if ($now->lt($startAt)) {
            $timeStatus = 'Sesi belum dapat dibuka. Jadwal dimulai pukul ' . $startAt->format('H:i') . ' WIB.';
            $buttonText = 'Belum Waktunya';
        } elseif ($now->gt($endAt)) {
            $timeStatus = 'Sesi tidak dapat dibuka karena jam pelajaran sudah selesai.';
            $buttonText = 'Jadwal Selesai';
        } else {
            $timeStatus = 'Jadwal sedang berlangsung. Sesi absensi dapat dibuka sekarang.';
            $buttonText = 'Buka Sesi Absensi';
            $canStart = true;
        }
    }

    $hasOpenSession = ($openSession ?? null) && $openSession->isOpen();
@endphp

<section class="teacher-session-hero">
    <div>
        <span class="section-kicker">SESI QR CODE</span>
        <h1>Sesi Absensi Guru</h1>
        <p>Sesi absensi dibuka sesuai jadwal mengajar hari ini. Guru dapat memilih jadwal secara manual apabila diperlukan.</p>
    </div>

    @if ($hasOpenSession)
        <a class="teacher-session-primary-link" href="{{ route('teacher.sessions.show', $openSession) }}">
            Tampilkan QR Aktif
        </a>
    @endif
</section>

@if ($errors->any())
    <div class="teacher-session-alert error">
        <strong>Sesi belum dapat dibuka.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="teacher-session-alert success">
        {{ session('success') }}
    </div>
@endif

@if ($hasOpenSession)
    <section class="teacher-active-session-banner">
        <strong>Sesi aktif:</strong>
        <span>{{ $openSession->subject->name ?? '-' }} · {{ $openSession->schoolClass->name ?? '-' }}</span>
        <a href="{{ route('teacher.sessions.show', $openSession) }}">Tampilkan QR</a>
    </section>
@endif

<section class="teacher-session-grid">
    <article class="teacher-session-card">
        <div>
            <span class="section-kicker">JADWAL SAAT INI</span>

            @if ($current)
                <h2>{{ $current->subject->name ?? '-' }}</h2>

                <div class="teacher-session-badges">
                    <span>{{ $todayName }}</span>
                    <span>{{ substr((string) $current->start_time, 0, 5) }}–{{ substr((string) $current->end_time, 0, 5) }}</span>
                    <span>{{ $current->schoolClass->name ?? '-' }}</span>
                </div>

                <p>{{ $timeStatus }}</p>
            @else
                <h2>Belum Ada Jadwal</h2>
                <p>{{ $timeStatus }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('teacher.sessions.store') }}" class="teacher-session-form">
            @csrf

            @if ($current)
                <input type="hidden" name="schedule_id" value="{{ $current->id }}">
            @endif

            <label>
                <span>Batas Hadir Normal</span>
                <div class="teacher-minute-input">
                    <input type="number" name="late_after_minutes" value="{{ old('late_after_minutes', 5) }}" min="1" max="120">
                    <span>menit</span>
                </div>
                <small>Lewat dari batas ini, siswa tercatat terlambat.</small>
            </label>

            <label>
                <span>Sesi Berakhir Setelah</span>
                <div class="teacher-minute-input">
                    <input type="number" name="session_duration_minutes" value="{{ old('session_duration_minutes', 15) }}" min="5" max="120">
                    <span>menit</span>
                </div>
                <small>Setelah durasi ini, QR tidak dapat dipakai scan lagi.</small>
            </label>

            <button type="submit" @disabled(! $canStart || $hasOpenSession || ! $current)>
                {{ $hasOpenSession ? 'Sesi Masih Aktif' : $buttonText }}
            </button>
        </form>
    </article>

    <article class="teacher-manual-session-card">
        <span class="section-kicker">BUKA SESI MANUAL</span>
        <h2>Pilih Jadwal Hari Ini</h2>
        <p>Pilih salah satu jadwal mengajar hari ini untuk membuka sesi absensi secara manual. Sesi tetap hanya dapat dibuka saat jam pelajaran berlangsung.</p>

        <form method="POST" action="{{ route('teacher.sessions.store') }}" class="teacher-manual-session-form" id="manual-session-form">
            @csrf

            <label>
                <span>Jadwal mengajar</span>
                <select name="schedule_id" id="manual-schedule-select">
                    <option value="">Pilih jadwal hari ini</option>
                    @foreach ($todayScheduleList as $schedule)
                        @php
                            $scheduleStart = \Illuminate\Support\Carbon::parse($now->toDateString() . ' ' . substr((string) $schedule->start_time, 0, 8));
                            $scheduleEnd = \Illuminate\Support\Carbon::parse($now->toDateString() . ' ' . substr((string) $schedule->end_time, 0, 8));
                            $scheduleCanOpen = $now->betweenIncluded($scheduleStart, $scheduleEnd);
                        @endphp

                        <option
                            value="{{ $schedule->id }}"
                            data-can-open="{{ $scheduleCanOpen ? '1' : '0' }}"
                            data-start="{{ $scheduleStart->format('H:i') }}"
                            data-end="{{ $scheduleEnd->format('H:i') }}"
                            @selected(old('schedule_id') == $schedule->id)
                        >
                            {{ $schedule->subject->name ?? '-' }} · {{ $schedule->schoolClass->name ?? '-' }} · {{ $scheduleStart->format('H:i') }}–{{ $scheduleEnd->format('H:i') }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Batas Hadir Normal</span>
                <div class="teacher-minute-input">
                    <input type="number" name="late_after_minutes" value="{{ old('late_after_minutes', 5) }}" min="1" max="120">
                    <span>menit</span>
                </div>
            </label>

            <label>
                <span>Sesi Berakhir Setelah</span>
                <div class="teacher-minute-input">
                    <input type="number" name="session_duration_minutes" value="{{ old('session_duration_minutes', 15) }}" min="5" max="120">
                    <span>menit</span>
                </div>
            </label>

            <div class="teacher-manual-session-note" id="manual-session-note">
                Pilih jadwal hari ini terlebih dahulu.
            </div>

            <button type="submit" id="manual-session-submit" @disabled($hasOpenSession)>
                {{ $hasOpenSession ? 'Sesi Masih Aktif' : 'Buka Sesi Absensi' }}
            </button>
        </form>
    </article>
</section>

<section class="teacher-session-history card table-wrap">
    <h2>Riwayat Sesi</h2>
    <p>Sesi terbaru ditampilkan paling atas.</p>

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
        @forelse (($sessions ?? collect()) as $session)
            <tr>
                <td>{{ optional($session->opened_at)->format('d-m-Y H:i') ?? '-' }}</td>
                <td>{{ $session->schoolClass->name ?? '-' }}</td>
                <td><strong>{{ $session->subject->name ?? '-' }}</strong></td>
                <td>
                    <span class="badge {{ $session->isOpen() ? 'green' : 'red' }}">
                        {{ $session->isOpen() ? 'Aktif' : 'Ditutup' }}
                    </span>
                </td>
                <td>
                    <a class="btn sm" href="{{ route('teacher.sessions.show', $session) }}">Buka</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="empty">Belum ada riwayat sesi absensi.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('manual-schedule-select');
    const button = document.getElementById('manual-session-submit');
    const note = document.getElementById('manual-session-note');

    if (!select || !button || !note) return;

    const hasOpenSession = @json($hasOpenSession);

    function syncManualButton() {
        const option = select.options[select.selectedIndex];
        const hasValue = Boolean(select.value);
        const canOpen = option && option.dataset.canOpen === '1';

        if (hasOpenSession) {
            button.disabled = true;
            note.textContent = 'Masih ada sesi absensi aktif.';
            note.className = 'teacher-manual-session-note warning';
            return;
        }

        if (!hasValue) {
            button.disabled = true;
            note.textContent = 'Pilih jadwal hari ini terlebih dahulu.';
            note.className = 'teacher-manual-session-note';
            return;
        }

        if (!canOpen) {
            button.disabled = true;
            note.textContent = 'Jadwal ini belum dimulai atau sudah selesai. Sesi hanya dapat dibuka pada jam pelajaran berlangsung.';
            note.className = 'teacher-manual-session-note warning';
            return;
        }

        button.disabled = false;
        note.textContent = 'Jadwal sedang berlangsung. Sesi absensi dapat dibuka.';
        note.className = 'teacher-manual-session-note success';
    }

    select.addEventListener('change', syncManualButton);
    syncManualButton();
});
</script>
@endsection

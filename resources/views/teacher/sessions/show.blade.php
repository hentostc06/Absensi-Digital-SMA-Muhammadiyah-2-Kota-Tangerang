@extends('layouts.app')

@section('title', 'QR Absensi Dinamis')

@section('content')
    <section class="session-hero">
        <div class="session-hero-content">
            <div class="session-breadcrumb">
                <a href="{{ route('teacher.sessions.index') }}">Sesi Absensi</a>
                <span>/</span>
                <span>QR Dinamis</span>
            </div>

            <div class="session-title-row">
                <div>
                    <span class="session-eyebrow">SESI PEMBELAJARAN</span>

                    <h1>
                        {{ $session->subject->name }}
                        <span>—</span>
                        {{ $session->schoolClass->name }}
                    </h1>

                    <p>
                        Tampilkan QR Code kepada siswa. Kode diperbarui otomatis
                        setiap 30 detik untuk mencegah penyalahgunaan.
                    </p>
                </div>

                <div class="session-actions">
                    @if ($session->isOpen())
                        <span class="live-status">
                            <i></i>
                            Sesi sedang aktif
                        </span>

                        <form
                            method="POST"
                            action="{{ route('teacher.sessions.close', $session) }}"
                            onsubmit="return confirm('Tutup sesi absensi ini? Siswa tidak dapat melakukan scan lagi.')"
                        >
                            @csrf

                            <button class="btn danger session-close-button" type="submit">
                                Tutup Sesi
                            </button>
                        </form>
                    @else
                        <span class="closed-status">Sesi telah ditutup</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="session-summary-grid">
        <div class="summary-item">
            <span>Mata Pelajaran</span>
            <strong>{{ $session->subject->name }}</strong>
        </div>

        <div class="summary-item">
            <span>Kelas</span>
            <strong>{{ $session->schoolClass->name }}</strong>
        </div>

        <div class="summary-item">
            <span>Dibuka</span>
            <strong>{{ $session->opened_at?->format('H:i') }} WIB</strong>
        </div>

        <div class="summary-item">
            <span>Batas Terlambat</span>
            <strong>{{ $session->late_after_minutes }} menit</strong>
        </div>
    </section>

    <div class="session-workspace">
        <article class="qr-panel">
            <div class="panel-heading">
                <div>
                    <span class="panel-kicker">QR CODE SISWA</span>
                    <h2>Pindai untuk melakukan absensi</h2>
                </div>

                <span
                    id="qr-status"
                    class="qr-status {{ $session->isOpen() ? 'online' : 'offline' }}"
                >
                    {{ $session->isOpen() ? 'Aktif' : 'Ditutup' }}
                </span>
            </div>

            @if ($session->isOpen())
                <div class="qr-frame">
                    <div id="qr-loader" class="qr-loader">
                        <span></span>
                        <p>Membuat QR Code aman...</p>
                    </div>

                    <img
                        id="qr-image"
                        src=""
                        alt="QR Code absensi dinamis"
                    >
                </div>

                <div class="countdown-section">
                    <div class="countdown-ring" id="countdown-ring">
                        <strong id="countdown">30</strong>
                        <span>detik</span>
                    </div>

                    <div class="countdown-copy">
                        <strong>QR Code diperbarui otomatis</strong>
                        <span>
                            Jangan mengambil foto QR karena kode lama akan langsung kedaluwarsa.
                        </span>
                    </div>
                </div>

                <div class="server-time-box">
                    <span class="server-time-icon">◷</span>

                    <div>
                        <span>Waktu server</span>
                        <strong id="server-time">Memuat...</strong>
                    </div>
                </div>
            @else
                <div class="closed-session-placeholder">
                    <span>✓</span>
                    <h3>Sesi Absensi Telah Ditutup</h3>
                    <p>QR Code sudah tidak dapat digunakan oleh siswa.</p>
                </div>
            @endif
        </article>

        <article class="attendance-panel">
            <div class="panel-heading attendance-heading">
                <div>
                    <span class="panel-kicker">MONITORING LANGSUNG</span>
                    <h2>Kehadiran Real-time</h2>
                    <p>Daftar ini diperbarui otomatis setiap 3 detik.</p>
                </div>

                <div class="attendance-counter">
                    <strong id="attendance-count">
                        {{ $session->attendances->count() }}
                    </strong>
                    <span>siswa hadir</span>
                </div>
            </div>

            <div class="attendance-table-wrapper">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Status</th>
                            <th>Waktu Scan</th>
                        </tr>
                    </thead>

                    <tbody id="attendance-list">
                        @forelse ($session->attendances as $attendance)
                            <tr>
                                <td>
                                    <span class="nis-cell">
                                        {{ $attendance->student->nis }}
                                    </span>
                                </td>

                                <td>
                                    <div class="student-cell">
                                        <span class="student-avatar">
                                            {{ strtoupper(substr($attendance->student->user->name, 0, 1)) }}
                                        </span>

                                        <strong>
                                            {{ $attendance->student->user->name }}
                                        </strong>
                                    </div>
                                </td>

                                <td>
                                    <span class="attendance-badge {{ $attendance->status }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $attendance->scanned_at?->format('H:i:s') }}
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-attendance-row">
                                <td colspan="4">
                                    <div class="attendance-empty-state">
                                        <span>⌁</span>
                                        <strong>Belum ada siswa yang melakukan scan</strong>
                                        <p>Data akan muncul otomatis setelah siswa berhasil memindai QR.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="attendance-footer">
                <span>
                    <i class="sync-dot"></i>
                    Sinkronisasi otomatis aktif
                </span>

                <small id="last-sync">Menunggu pembaruan...</small>
            </div>
        </article>
    </div>
@endsection

@push('scripts')
    @if ($session->isOpen())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                dynamicQr({
                    tokenUrl: @json(route('teacher.sessions.token', $session)),
                    attendanceUrl: @json(route('teacher.sessions.attendance', $session))
                });
            });
        </script>
    @endif
@endpush

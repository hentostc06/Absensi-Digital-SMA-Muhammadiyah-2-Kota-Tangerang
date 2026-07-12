@extends('layouts.app')

@section('title', 'QR Absensi Dinamis')

@section('content')
<section class="qr-show-hero">
    <div>
        <span class="section-kicker">QR ABSENSI DINAMIS</span>
        <h1>QR Absensi Siswa</h1>
        <p>
            Tampilkan QR Code kepada siswa. Kode diperbarui otomatis setiap 30 detik untuk menjaga keamanan sesi absensi.
        </p>

        <div class="qr-hero-meta">
            <span>{{ $session->subject->name }}</span>
            <span>{{ $session->schoolClass->name }}</span>
            <span>{{ $session->opened_at->format('H:i') }} WIB</span>
        </div>
    </div>

    <div class="qr-hero-actions">
        <a href="{{ route('teacher.sessions.index') }}" class="btn">Kembali</a>

        @if ($session->isOpen())
            <form method="POST" action="{{ route('teacher.sessions.close', $session) }}" data-confirm="Tutup sesi absensi ini?">
                @csrf
                <button type="submit" class="btn danger">Tutup Sesi</button>
            </form>
        @else
            <span class="closed-status">Sesi Ditutup</span>
        @endif
    </div>
</section>

<section class="qr-session-summary">
    <div class="qr-summary-card">
        <span>Mata Pelajaran</span>
        <strong>{{ $session->subject->name }}</strong>
    </div>

    <div class="qr-summary-card">
        <span>Kelas</span>
        <strong>{{ $session->schoolClass->name }}</strong>
    </div>

    <div class="qr-summary-card">
        <span>Dibuka</span>
        <strong>{{ $session->opened_at->format('H:i') }} WIB</strong>
    </div>

    <div class="qr-summary-card">
        <span>Batas Terlambat</span>
        <strong>{{ $session->late_after_minutes }} menit</strong>
    </div>
</section>

<section class="qr-show-grid">
    <div class="qr-show-card qr-display-card">
        <div class="qr-card-head">
            <div>
                <span class="section-kicker">QR CODE SISWA</span>
                <h2>Pindai untuk Absensi</h2>
            </div>

            <span id="qr-status" class="qr-status {{ $session->isOpen() ? 'online' : 'offline' }}">
                {{ $session->isOpen() ? 'Aktif' : 'Ditutup' }}
            </span>
        </div>

        <div class="qr-frame">
            <div id="qr-loader" class="qr-loader">
                <span>Memuat QR Code...</span>
            </div>
            <img id="qr-image" alt="QR Code Absensi">
        </div>

        <div class="qr-footer-panel">
            <div class="countdown-ring" id="countdown-ring">
                <strong id="countdown">30</strong>
            </div>

            <div class="qr-footer-text">
                <strong>QR Code diperbarui otomatis</strong>
                <p>Jangan mengambil foto QR karena kode lama akan langsung kedaluwarsa.</p>

                <div class="qr-server-time">
                    <span>Waktu server</span>
                    <b id="server-time">-</b>
                </div>
            </div>
        </div>
    </div>

    <div class="qr-show-card qr-monitor-card">
        <div class="qr-card-head">
            <div>
                <span class="section-kicker">MONITORING LANGSUNG</span>
                <h2>Kehadiran Real-time</h2>
                <p>Daftar ini diperbarui otomatis setiap 3 detik.</p>
            </div>

            <div class="attendance-counter">
                <strong id="attendance-count">0</strong>
                <span>siswa hadir</span>
            </div>
        </div>

        <div class="attendance-table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Status</th>
                    <th>Waktu Scan</th>
                </tr>
                </thead>
                <tbody id="attendance-list">
                <tr>
                    <td colspan="4">
                        <div class="attendance-empty-state">
                            <strong>Belum ada siswa yang melakukan scan</strong>
                            <p>Data akan muncul otomatis setelah siswa berhasil memindai QR.</p>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="qr-sync-footer">
            <span>Sinkronisasi otomatis aktif</span>
            <b id="last-sync">Menunggu data...</b>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    dynamicQr({
        tokenUrl: @json(route('teacher.sessions.token', $session)),
        attendanceUrl: @json(route('teacher.sessions.attendance', $session))
    });
});
</script>
@endpush

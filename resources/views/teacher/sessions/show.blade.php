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
        <span>Terlambat Setelah</span>
        <strong>{{ $session->late_after_minutes ?? 5 }} menit</strong>
    </div>

    <div class="qr-summary-card">
        <span>Sesi Berakhir</span>
        <strong>{{ $session->session_duration_minutes ?? 15 }} menit</strong>
    </div>
</section>

<section class="qr-show-grid">
    <div class="qr-show-card qr-display-card">
        <div class="qr-card-head projector-ready">
            <div>
                <span class="section-kicker">QR CODE SISWA</span>
                <h2>Pindai untuk Absensi</h2>
            </div>

            <div class="qr-card-actions">
                <button type="button" class="qr-projector-open" id="open-projector">
                    Mode Proyektor
                </button>

                <span id="qr-status" class="qr-status {{ $session->isOpen() ? 'online' : 'offline' }}">
                    {{ $session->isOpen() ? 'Aktif' : 'Ditutup' }}
                </span>
            </div>
        </div>

        <div class="qr-frame">
            <div id="qr-loader" class="qr-loader">
                <span>Memuat QR Code...</span>
            </div>
            <img id="qr-image" alt="QR Code Absensi">
        </div>

        <div class="qr-footer-panel">
            <svg id="countdown-ring" class="qr-countdown-svg" viewBox="0 0 100 100" aria-label="Countdown refresh QR">
    <circle class="qr-countdown-bg" cx="50" cy="50" r="40"></circle>
    <circle id="countdown-progress" class="qr-countdown-progress" cx="50" cy="50" r="40"></circle>
    <circle class="qr-countdown-inner" cx="50" cy="50" r="31"></circle>
    <text id="countdown" class="qr-countdown-number" x="50" y="53" text-anchor="middle">30</text>
</svg>

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

<div class="qr-projector-modal" id="projector-modal" hidden>
    <div class="qr-projector-dialog">
        <button type="button" class="qr-projector-close" id="close-projector" aria-label="Tutup">×</button>

        <header class="qr-projector-header">
            <div class="qr-projector-brand">
                <img src="{{ asset('images/logo-sma-muhammadiyah-2.jpeg') }}" alt="Logo Sekolah">
                <div>
                    <h2>Sistem Absensi QR</h2>
                    <p>SMA Muhammadiyah 2 Kota Tangerang</p>
                </div>
            </div>

            <div class="qr-projector-meta">
                <span>{{ $session->subject->name }}</span>
                <span>{{ $session->schoolClass->name }}</span>
                <span>Scan QR untuk absensi</span>
            </div>
        </header>

        <main class="qr-projector-body">
            <div class="qr-projector-box">
                <img id="projector-qr-image" alt="QR Code Proyektor">
            </div>
        </main>

        <footer class="qr-projector-footer">
            <svg id="projector-countdown-ring" class="qr-projector-countdown-svg" viewBox="0 0 100 100" aria-label="Countdown refresh QR">
    <circle class="qr-projector-countdown-bg" cx="50" cy="50" r="40"></circle>
    <circle id="projector-countdown-progress" class="qr-projector-countdown-progress" cx="50" cy="50" r="40"></circle>
    <circle class="qr-projector-countdown-inner" cx="50" cy="50" r="31"></circle>
    <text id="projector-countdown" class="qr-projector-countdown-number" x="50" y="53" text-anchor="middle">30</text>
</svg></footer>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    dynamicQr({
        tokenUrl: @json(route('teacher.sessions.token', $session)),
        attendanceUrl: @json(route('teacher.sessions.attendance', $session))
    });

    const openButton = document.getElementById('open-projector');
    const closeButton = document.getElementById('close-projector');
    const modal = document.getElementById('projector-modal');
    const qrImage = document.getElementById('qr-image');
    const projectorImage = document.getElementById('projector-qr-image');
    const countdown = document.getElementById('countdown');
    const projectorCountdown = document.getElementById('projector-countdown');
    const ring = document.getElementById('countdown-ring');
    const projectorRing = document.getElementById('projector-countdown-ring');
    const serverTime = document.getElementById('server-time');
    const projectorServerTime = document.getElementById('projector-server-time');

    function syncProjector() {
        if (qrImage && projectorImage && qrImage.src) {
            projectorImage.src = qrImage.src;
        }

        if (countdown && projectorCountdown) {
            projectorCountdown.textContent = countdown.textContent || '30';
        }

        if (ring && projectorRing) {
            const progress = ring.style.getPropertyValue('--progress') || '360deg';
            projectorRing.style.setProperty('--projector-progress', progress);
        }

        if (serverTime && projectorServerTime) {
            projectorServerTime.textContent = serverTime.textContent || '-';
        }
    }

    function openProjector() {
        syncProjector();
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeProjector() {
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    openButton?.addEventListener('click', openProjector);
    closeButton?.addEventListener('click', closeProjector);

    modal?.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeProjector();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeProjector();
        }
    });

    setInterval(function () {
        if (modal && !modal.hidden) {
            syncProjector();
        }
    }, 500);
});
</script>
@endpush

@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
    @php
        $items = collect($recent ?? $recentAttendances ?? $attendances ?? $history ?? []);
    @endphp

    <div class="student-scan-page">
        <div class="page-heading scan-page-heading">
            <div>
                <div class="scan-intro-card">
<span class="section-kicker">Absensi Siswa</span>
                <h1>Scan QR Code</h1>
                <p>Gunakan kamera belakang HP untuk memindai QR Code yang ditampilkan guru. Setelah QR terbaca, sistem akan mengirim absensi otomatis.</p>
</div>
            </div>
        </div>

        <div class="scan-layout-grid">
            <section class="scan-card">
                <div class="scanner-toolbar">
                    <select id="camera-device">
                        <option value="">Otomatis kamera belakang</option>
                    </select>

                    <button type="button" id="camera-start">Aktifkan Kamera</button>
                    <button type="button" id="camera-switch">Ganti Kamera</button>
                    <button type="button" id="camera-stop">Stop</button>
                </div>

                <div id="reader" class="qr-reader-box">
                    <div class="reader-placeholder">Kamera belum aktif</div>
                </div>

                <div id="scan-feedback" class="scan-feedback" data-type="info">
                    Arahkan kamera ke QR Code yang ditampilkan guru.
                </div>

                <details class="manual-token-box">
                    <summary>Masukkan token manual untuk testing</summary>

                    <form method="POST" action="{{ route('student.scan.store') }}" class="manual-token-form">
                        @csrf
                        <textarea name="token" rows="3" placeholder="Tempel token QR di sini" required></textarea>
                        <button type="submit">Kirim Token</button>
                    </form>
                </details>
            </section>

            <section class="scan-card">
                <div class="scan-card-head">
                    <h2>Absensi Terbaru</h2>
                    <p>Riwayat hasil scan terakhir siswa.</p>
                </div>

                <div class="scan-history-list">
                    @forelse ($items as $attendance)
                        @php
                            $subjectName =
                                $attendance->subject_name
                                ?? data_get($attendance, 'subject.name')
                                ?? data_get($attendance, 'session.subject.name')
                                ?? data_get($attendance, 'session.subject_name')
                                ?? data_get($attendance, 'session.lesson_name')
                                ?? data_get($attendance, 'session.mapel')
                                ?? 'Mata Pelajaran';

                            $statusRaw = $attendance->status ?? 'hadir';
                            $statusLabel = ucfirst(str_replace('_', ' ', $statusRaw));

                            $rawTime = $attendance->scanned_at
                                ?? $attendance->created_at
                                ?? $attendance->updated_at
                                ?? null;

                            try {
                                $timeLabel = $rawTime
                                    ? \Illuminate\Support\Carbon::parse($rawTime)->format('d/m/Y H:i')
                                    : '-';
                            } catch (\Throwable $exception) {
                                $timeLabel = '-';
                            }
                        @endphp

                        <article class="scan-history-item">
                            <div class="scan-history-avatar">
                                {{ strtoupper(substr($statusLabel, 0, 1)) }}
                            </div>

                            <div class="scan-history-content">
                                <strong>{{ $subjectName }}</strong>
                                <span>{{ $statusLabel }} • {{ $timeLabel }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <strong>Belum ada absensi terbaru.</strong>
                            <p>Riwayat akan muncul setelah QR Code berhasil discan.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.studentScanner) {
                window.studentScanner(@json(route('student.scan.store')));
            }
        });
    </script>
@endsection

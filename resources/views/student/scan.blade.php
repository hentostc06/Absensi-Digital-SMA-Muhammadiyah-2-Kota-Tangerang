@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="page-head">
    <div>
        <span class="section-kicker">ABSENSI SISWA</span>
        <h1>Scan QR Code</h1>
        <p>Gunakan kamera belakang HP untuk memindai QR Code yang ditampilkan guru. Setelah QR terbaca, sistem akan mengirim absensi otomatis.</p>
    </div>
</div>

<div class="scan-layout">
    <div class="card scanner-card">
        <div class="scanner-toolbar">
            <select id="camera-device" aria-label="Pilih kamera">
                <option value="">Otomatis kamera belakang</option>
            </select>

            <button type="button" id="camera-start" class="btn primary">Aktifkan Kamera</button>
            <button type="button" id="camera-switch" class="btn" disabled>Ganti Kamera</button>
            <button type="button" id="camera-stop" class="btn" disabled>Stop</button>
        </div>

        <div id="reader">
            <span class="muted">Kamera belum aktif</span>
        </div>

        <div id="scan-feedback" class="scan-feedback">
            <div class="icon">i</div>
            <strong>Siap memindai</strong>
            <span>Tekan Aktifkan Kamera. Jika muncul izin kamera, pilih Izinkan.</span>
        </div>

        <details>
            <summary>Masukkan token manual untuk testing</summary>
            <form id="manual-form" class="form" style="margin-top:12px">
                <textarea id="manual-token" placeholder="Tempel isi QR/token di sini"></textarea>
                <button type="submit" class="btn primary">Kirim Token</button>
            </form>
        </details>
    </div>

    <div class="card">
        <h3>Absensi Terbaru</h3>
        <div class="history-list">
            @forelse ($recent as $attendance)
                <div>
                    <strong>{{ $attendance->session->subject->name }}</strong>
                    <span>{{ optional($attendance->scanned_at)->format('d-m-Y H:i') }} — {{ ucfirst($attendance->status) }}</span>
                </div>
            @empty
                <p class="muted">Belum ada riwayat absensi.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    studentScanner(@json(route('student.scan.store')));
});
</script>
@endpush

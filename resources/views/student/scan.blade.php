@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
    <div class="page-head">
        <div>
            <h1>Scan QR Code</h1>
            <p>
                Arahkan kamera ke QR Code dinamis yang ditampilkan oleh guru.
            </p>
        </div>
    </div>

    <div class="scan-layout">
        <div class="card scanner-card">
            <div id="reader"></div>

            <div id="scan-feedback" class="scan-feedback">
                <div class="icon">⌁</div>
                <strong>Siap memindai</strong>
                <span>Izinkan akses kamera pada browser.</span>
            </div>

            <details>
                <summary>Masukkan token secara manual</summary>

                <form id="manual-form" class="form">
                    <textarea
                        id="manual-token"
                        placeholder="Tempel token QR Code di sini"
                    ></textarea>

                    <button type="submit" class="btn primary">
                        Kirim
                    </button>
                </form>
            </details>
        </div>

        <div class="card">
            <h3>Absensi Terbaru</h3>

            <div class="history-list">
                @forelse ($recent as $attendance)
                    <div>
                        <strong>
                            {{ $attendance->session->subject->name }}
                        </strong>

                        <span>
                            {{ optional($attendance->scanned_at)->format('d-m-Y H:i') }}
                            —
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </div>
                @empty
                    <p class="muted">
                        Belum ada riwayat absensi.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('head')
    <script
        src="https://unpkg.com/html5-qrcode"
        defer
    ></script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            studentScanner(@json(route('student.scan.store')));
        });
    </script>
@endpush

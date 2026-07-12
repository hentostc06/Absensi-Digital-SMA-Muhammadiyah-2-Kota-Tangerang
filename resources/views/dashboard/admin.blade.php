@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<section class="admin-dashboard-hero">
    <div class="admin-dashboard-hero-text">
        <span class="section-kicker">Dashboard Admin</span>
        <h1>Dashboard Absensi Digital</h1>
        <p>Kelola akun, data akademik, jadwal, sesi absensi, dan laporan dalam satu panel yang rapi.</p>
    </div>

    <a href="{{ route('admin.accounts.index') }}" class="admin-dashboard-primary-button">
        Kelola Akun Baru
    </a>
</section>

<section class="admin-stat-grid">
    <div class="admin-stat-card">
        <span>Total Akun</span>
        <strong>{{ $accounts ?? 0 }}</strong>
    </div>

    <div class="admin-stat-card">
        <span>Total Siswa</span>
        <strong>{{ $students ?? 0 }}</strong>
    </div>

    <div class="admin-stat-card">
        <span>Total Guru</span>
        <strong>{{ $teachers ?? 0 }}</strong>
    </div>

    <div class="admin-stat-card">
        <span>Hadir Hari Ini</span>
        <strong>{{ $today ?? 0 }}</strong>
    </div>

    <div class="admin-stat-card">
        <span>Terlambat</span>
        <strong>{{ $lateToday ?? 0 }}</strong>
    </div>

    <div class="admin-stat-card">
        <span>Sesi Aktif</span>
        <strong>{{ $openSessions ?? 0 }}</strong>
    </div>
</section>

<section class="admin-dashboard-grid">
    <div class="admin-dashboard-panel">
        <div class="admin-panel-head">
            <div>
                <span class="section-kicker">Akses Cepat</span>
                <h2>Menu Operasional</h2>
            </div>
        </div>

        <div class="admin-quick-list">
            <a href="{{ route('admin.accounts.index') }}" class="admin-quick-card">
                <span class="admin-quick-icon">A</span>
                <div>
                    <strong>Kelola Akun</strong>
                    <p>Buat akun admin, guru, dan siswa otomatis.</p>
                </div>
            </a>

            <a href="{{ route('admin.schedules.index') }}" class="admin-quick-card primary">
                <span class="admin-quick-icon">J</span>
                <div>
                    <strong>Tambah Jadwal</strong>
                    <p>Atur jadwal guru, mata pelajaran, dan kelas.</p>
                </div>
            </a>

            <a href="{{ route('admin.reports.index') }}" class="admin-quick-card">
                <span class="admin-quick-icon">L</span>
                <div>
                    <strong>Laporan Absensi</strong>
                    <p>Rekap absensi harian dan bulanan.</p>
                </div>
            </a>
        </div>
    </div>

    <div class="admin-dashboard-panel">
        <div class="admin-panel-head">
            <div>
                <span class="section-kicker">Aktivitas</span>
                <h2>Akun Terbaru</h2>
            </div>
        </div>

        <div class="admin-recent-list">
            @forelse ($recentAccounts as $account)
                <div class="admin-recent-card">
                    <div class="admin-recent-avatar">
                        {{ strtoupper(substr($account->name ?? 'A', 0, 1)) }}
                    </div>

                    <div class="admin-recent-info">
                        <strong>{{ $account->name }}</strong>
                        <p>{{ $account->username }} · {{ strtoupper($account->role) }}</p>
                    </div>
                </div>
            @empty
                <div class="empty-state">Belum ada akun terbaru.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection

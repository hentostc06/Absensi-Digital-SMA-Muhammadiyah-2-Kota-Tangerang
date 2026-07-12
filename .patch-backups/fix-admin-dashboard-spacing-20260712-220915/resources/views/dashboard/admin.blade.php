@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<div class="hero-dashboard">
    <div>
        <span class="section-kicker">PUSAT KENDALI ADMIN</span>
        <h1>Dashboard Absensi Digital</h1>
        <p>Kelola akun, data akademik, jadwal, sesi absensi, dan laporan dalam satu panel.</p>
    </div>
    <a href="{{ route('admin.accounts.index') }}" class="btn primary">Kelola Akun Baru</a>
</div>

<div class="stats premium-stats">
    <div class="stat"><span>Total Akun</span><strong>{{ $accounts }}</strong></div>
    <div class="stat"><span>Total Siswa</span><strong>{{ $students }}</strong></div>
    <div class="stat"><span>Total Guru</span><strong>{{ $teachers }}</strong></div>
    <div class="stat"><span>Hadir Hari Ini</span><strong>{{ $today }}</strong></div>
    <div class="stat"><span>Terlambat</span><strong>{{ $lateToday }}</strong></div>
    <div class="stat"><span>Sesi Aktif</span><strong>{{ $openSessions }}</strong></div>
</div>

<div class="dashboard-grid">
    <div class="card glass-panel">
        <h3>Akses Cepat</h3>
        <div class="quick-actions">
            <a class="quick-card" href="{{ route('admin.accounts.index') }}"><span>◉</span><div><strong>Kelola Akun</strong><small>Buat akun admin, guru, dan siswa otomatis</small></div></a>
            <a class="quick-card" href="{{ route('admin.schedules.create') }}"><span>◷</span><div><strong>Tambah Jadwal</strong><small>Atur jadwal guru dan kelas</small></div></a>
            <a class="quick-card" href="{{ route('admin.reports.index') }}"><span>▥</span><div><strong>Laporan</strong><small>Rekap absensi harian/bulanan</small></div></a>
        </div>
    </div>

    <div class="card glass-panel">
        <h3>Akun Terbaru</h3>
        <div class="timeline-list">
            @forelse ($recentAccounts as $account)
                <div class="timeline-item">
                    <div class="timeline-avatar">{{ strtoupper(substr($account->name, 0, 1)) }}</div>
                    <div><strong>{{ $account->name }}</strong><span>{{ $account->username }} • {{ strtoupper($account->role) }}</span></div>
                </div>
            @empty
                <p class="muted">Belum ada akun.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

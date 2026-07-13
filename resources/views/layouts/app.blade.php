<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | SMA Muhammadiyah 2 Kota Tangerang</title>
    <link rel="icon" href="{{ asset('images/logo-sma-muhammadiyah-2.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="school-brand">
            <img src="{{ asset('images/logo-sma-muhammadiyah-2.jpeg') }}" alt="Logo" class="school-logo">
            <div class="school-brand-text">
                <strong>Absensi Sekolah</strong>
                <span>SMA Muhammadiyah 2</span>
                <small>Kota Tangerang</small>
            </div>
        </a>

        <div class="sidebar-divider"></div>
        <p class="nav-label">MENU UTAMA</p>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="nav-icon">D</span><span>Dashboard</span></a>

            @if (auth()->user()->role === 'admin')
                <a href="{{ route('admin.accounts.index') }}" class="sidebar-link {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}"><span class="nav-icon">A</span><span>Kelola Akun</span></a>
                <a href="{{ route('admin.students.index') }}" class="sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><span class="nav-icon">S</span><span>Data Siswa</span></a>
                <a href="{{ route('admin.teachers.index') }}" class="sidebar-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><span class="nav-icon">G</span><span>Data Guru</span></a>
                <a href="{{ route('admin.classes.index') }}" class="sidebar-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}"><span class="nav-icon">K</span><span>Data Kelas</span></a>
                <a href="{{ route('admin.subjects.index') }}" class="sidebar-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><span class="nav-icon">M</span><span>Mata Pelajaran</span></a>
                <a href="{{ route('admin.schedules.index') }}" class="sidebar-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}"><span class="nav-icon">J</span><span>Jadwal Pelajaran</span></a>
                <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><span class="nav-icon">L</span><span>Laporan Absensi</span></a>
            @elseif (auth()->user()->role === 'guru')
                <a href="{{ route('teacher.sessions.index') }}"
   class="sidebar-link sidebar-qr-link {{ request()->routeIs('teacher.sessions.*') ? 'active' : '' }}">
    <span class="nav-icon" aria-hidden="true"></span>
    <span>Sesi QR Code</span>
</a>
                <a href="{{ url('/guru/jadwal-saya') }}" class="sidebar-link {{ request()->routeIs('teacher.schedule') ? 'active' : '' }}"><span class="nav-icon">J</span><span>Jadwal Saya</span></a>
            @elseif (auth()->user()->role === 'siswa')
                <a href="{{ route('student.schedule') }}" class="sidebar-link {{ request()->routeIs('student.schedule') ? 'active' : '' }}"><span class="nav-icon">J</span><span>Jadwal Saya</span></a>
                <a href="{{ route('student.scan') }}"
   class="sidebar-link sidebar-qr-link {{ request()->routeIs('student.scan*') ? 'active' : '' }}">
    <span class="nav-icon" aria-hidden="true"></span>
    <span>Scan QR Code</span>
</a>
                <a href="{{ route('student.history') }}" class="sidebar-link {{ request()->routeIs('student.history') ? 'active' : '' }}"><span class="nav-icon">R</span><span>Riwayat Absensi</span></a>
            @endif
        </nav>
                @auth
                    <div class="account-settings-sidebar-wrap">
                        @include('partials.account-settings-link')
                    </div>
                @endauth


        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="sidebar-user-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ strtoupper(auth()->user()->role) }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
    @csrf
    <button type="submit" class="sidebar-logout-button">Keluar</button>
</form>
        </div>
    </aside>

    <button class="sidebar-backdrop" type="button" onclick="document.body.classList.remove('menu-open')" aria-label="Tutup menu"></button>

    <div class="app-main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="mobile-menu-button" onclick="document.body.classList.toggle('menu-open')">☰</button>
                <div class="topbar-title-area">
                    <span class="topbar-caption">Sistem Absensi QR</span>
                    @include('partials.breadcrumbs')
                </div>
            </div>

            <div class="topbar-user">
                <div class="topbar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="topbar-user-text">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ strtoupper(auth()->user()->role) }}</span>
                </div>
            </div>
        </header>

        <main class="content">
            @if (session('generated_password'))
                <div class="alert info reveal-card">
                    <strong>Akun siap digunakan:</strong>
                    <span>{{ session('generated_password.name') }}</span>
                    <code>Username: {{ session('generated_password.username') }}</code>
                    <code>Password: {{ session('generated_password.password') }}</code>
                </div>
            @endif

            @if (session('success')) <div class="alert success">{{ session('success') }}</div> @endif
            @if (session('error')) <div class="alert danger">{{ session('error') }}</div> @endif
            @if ($errors->any()) <div class="alert danger">{{ $errors->first() }}</div> @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | SMA Muhammadiyah 2 Kota Tangerang</title>

    <link rel="icon" href="{{ asset('images/logo-sekolah.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">

    @stack('head')
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a href="{{ route('dashboard') }}" class="school-brand">
                <img
                    src="{{ asset('images/logo-sekolah.svg') }}"
                    alt="Logo SMA Muhammadiyah 2 Kota Tangerang"
                    class="school-logo"
                >

                <div class="school-brand-text">
                    <strong>Absensi Digital</strong>
                    <span>SMA Muhammadiyah 2</span>
                    <small>Kota Tangerang</small>
                </div>
            </a>

            <div class="sidebar-divider"></div>

            <p class="nav-label">MENU UTAMA</p>

            <nav class="sidebar-nav">
                <a
                    href="{{ route('dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                >
                    <span class="nav-icon">⌂</span>
                    <span>Dashboard</span>
                </a>

                @if (auth()->user()->role === 'admin')
                    <a
                        href="{{ route('admin.students.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">♙</span>
                        <span>Data Siswa</span>
                    </a>

                    <a
                        href="{{ route('admin.teachers.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">♟</span>
                        <span>Data Guru</span>
                    </a>

                    <a
                        href="{{ route('admin.classes.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">▦</span>
                        <span>Data Kelas</span>
                    </a>

                    <a
                        href="{{ route('admin.subjects.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">▤</span>
                        <span>Mata Pelajaran</span>
                    </a>

                    <a
                        href="{{ route('admin.schedules.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">◷</span>
                        <span>Jadwal Pelajaran</span>
                    </a>

                    <a
                        href="{{ route('admin.reports.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">▥</span>
                        <span>Laporan Absensi</span>
                    </a>
                @elseif (auth()->user()->role === 'guru')
                    <a
                        href="{{ route('teacher.sessions.index') }}"
                        class="sidebar-link {{ request()->routeIs('teacher.sessions.*') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">▣</span>
                        <span>Sesi dan QR Code</span>
                    </a>
                @elseif (auth()->user()->role === 'siswa')
                    <a
                        href="{{ route('student.scan') }}"
                        class="sidebar-link {{ request()->routeIs('student.scan') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">⌗</span>
                        <span>Scan QR Code</span>
                    </a>

                    <a
                        href="{{ route('student.history') }}"
                        class="sidebar-link {{ request()->routeIs('student.history') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">◴</span>
                        <span>Riwayat Absensi</span>
                    </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="sidebar-user-info">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ strtoupper(auth()->user()->role) }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="logout-button">
                        <span>↪</span>
                        Keluar dari Sistem
                    </button>
                </form>
            </div>
        </aside>

        <button
            class="sidebar-backdrop"
            type="button"
            aria-label="Tutup navigasi"
            onclick="document.body.classList.remove('menu-open')"
        ></button>

        <div class="app-main">
            <header class="topbar">
                <div class="topbar-left">
                    <button
                        type="button"
                        class="mobile-menu-button"
                        aria-label="Buka navigasi"
                        onclick="document.body.classList.toggle('menu-open')"
                    >
                        ☰
                    </button>

                    <div>
                        <span class="topbar-caption">Sistem Informasi Absensi</span>
                        <strong class="topbar-title">@yield('title', 'Dashboard')</strong>
                    </div>
                </div>

                <div class="topbar-user">
                    <div class="topbar-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="topbar-user-text">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ strtoupper(auth()->user()->role) }}</span>
                    </div>
                </div>
            </header>

            <main class="content">
                @if (session('success'))
                    <div class="alert success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>

    @stack('scripts')
</body>
</html>

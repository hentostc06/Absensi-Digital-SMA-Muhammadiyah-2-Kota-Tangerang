<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Absensi Digital') - SMA Muhammadiyah 2 Tangerang</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('head')
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">M2</div>

                <div>
                    <strong>Absensi Digital</strong>
                    <small>SMA Muhammadiyah 2 Tangerang</small>
                </div>
            </div>

            <nav>
                <a href="{{ route('dashboard') }}">
                    Dashboard
                </a>

                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.students.index') }}">
                        Data Siswa
                    </a>

                    <a href="{{ route('admin.teachers.index') }}">
                        Data Guru
                    </a>

                    <a href="{{ route('admin.classes.index') }}">
                        Data Kelas
                    </a>

                    <a href="{{ route('admin.subjects.index') }}">
                        Mata Pelajaran
                    </a>

                    <a href="{{ route('admin.schedules.index') }}">
                        Jadwal Pelajaran
                    </a>

                    <a href="{{ route('admin.reports.index') }}">
                        Laporan Absensi
                    </a>
                @elseif (auth()->user()->role === 'guru')
                    <a href="{{ route('teacher.sessions.index') }}">
                        Sesi dan QR Code
                    </a>
                @elseif (auth()->user()->role === 'siswa')
                    <a href="{{ route('student.scan') }}">
                        Scan QR Code
                    </a>

                    <a href="{{ route('student.history') }}">
                        Riwayat Absensi
                    </a>
                @endif
            </nav>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="logout">
                    Keluar
                </button>
            </form>
        </aside>

        <main>
            <header class="topbar">
                <button
                    type="button"
                    class="menu"
                    onclick="document.body.classList.toggle('menu-open')"
                >
                    ☰
                </button>

                <div>
                    <strong>{{ auth()->user()->name }}</strong>

                    <span class="role">
                        {{ strtoupper(auth()->user()->role) }}
                    </span>
                </div>
            </header>

            <section class="content">
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
            </section>
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>

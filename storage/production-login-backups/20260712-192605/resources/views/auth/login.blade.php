<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Absensi Digital</title>
    <link rel="icon" href="{{ asset('images/logo-sekolah.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="auth-shell single-login-shell">
        <section class="auth-visual">
            <div class="orb orb-one"></div>
            <div class="orb orb-two"></div>

            <img src="{{ asset('images/logo-sekolah.svg') }}" class="auth-logo" alt="Logo">

            <span class="auth-kicker">SISTEM ABSENSI QR DINAMIS</span>

            <h1>Satu pintu login untuk Admin, Guru, dan Siswa.</h1>

            <p>
                Pengguna cukup memasukkan username atau NIS dan password.
                Sistem akan membaca role akun secara otomatis dan mengarahkan ke dashboard masing-masing.
            </p>

            <div class="auth-feature-grid">
                <div>
                    <strong>Admin</strong>
                    <span>Mengelola akun, kelas, guru, siswa, jadwal, dan laporan.</span>
                </div>

                <div>
                    <strong>Guru</strong>
                    <span>Membuka sesi, menampilkan QR, dan memantau absensi.</span>
                </div>

                <div>
                    <strong>Siswa</strong>
                    <span>Login menggunakan NIS untuk scan QR dan melihat riwayat.</span>
                </div>
            </div>
        </section>

        <section class="login-card neo-card">
            <div class="brand center">
                <img src="{{ asset('images/logo-sekolah.svg') }}" class="login-school-logo" alt="Logo">

                <div>
                    <h1>Absensi Digital</h1>
                    <p>SMA Muhammadiyah 2 Kota Tangerang</p>
                </div>
            </div>

            <div class="login-intro">
                <span class="section-kicker">LOGIN PENGGUNA</span>
                <h2>Masuk ke Sistem</h2>
                <p>
                    Masukkan username untuk Admin/Guru atau NIS untuk Siswa.
                </p>
            </div>

            @if($errors->any())
                <div class="alert danger">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('login.store') }}" class="form auth-form">
                @csrf

                <label>
                    Username / NIS
                    <input
                        id="login-username"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Contoh: admin, guru.demo, atau 202601001"
                    >
                </label>

                <label>
                    Password
                    <div class="password-field">
                        <input
                            id="login-password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password"
                        >

                        <button type="button" data-toggle-password>
                            lihat
                        </button>
                    </div>
                </label>

                <div class="login-helper-row">
                    <label class="check">
                        <input type="checkbox" name="remember">
                        Ingat saya
                    </label>

                    <span>Akun dibuat oleh Admin</span>
                </div>

                <button class="btn primary wide auth-submit">
                    <span>Masuk Otomatis</span>
                    <i>→</i>
                </button>
            </form>

            <div class="demo smart-demo">
                <strong>Contoh akun demo</strong>
                <small>
                    admin / Admin123! • guru.demo / Guru123! • 19221273 / Siswa123!
                </small>
            </div>
        </section>
    </main>
</body>
</html>

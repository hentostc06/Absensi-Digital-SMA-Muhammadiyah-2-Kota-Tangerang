<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Sistem Absensi SMA Muhammadiyah 2 Kota Tangerang</title>
    <link rel="icon" href="{{ asset('images/logo-sekolah.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="production-login-page">
    <main class="production-login-shell">
        <section class="production-login-brand">
            <div class="production-brand-top">
                <img src="{{ asset('images/logo-sekolah.svg') }}" alt="Logo SMA Muhammadiyah 2 Kota Tangerang">

                <div>
                    <span>SMA Muhammadiyah 2 Kota Tangerang</span>
                    <strong>Sistem Informasi Absensi Siswa</strong>
                </div>
            </div>

            <div class="production-brand-content">
                <span class="production-label">PORTAL ABSENSI</span>

                <h1>
                    Verifikasi kehadiran siswa berbasis QR Code.
                </h1>

                <p>
                    Sistem digunakan untuk pengelolaan absensi harian oleh sekolah,
                    guru, dan siswa secara terpusat.
                </p>
            </div>

            <div class="production-brand-footer">
                <span>Admin</span>
                <span>Guru</span>
                <span>Siswa</span>
            </div>
        </section>

        <section class="production-login-panel">
            <div class="production-login-card">
                <div class="production-login-header">
                    <img src="{{ asset('images/logo-sekolah.svg') }}" alt="Logo">

                    <div>
                        <h2>Masuk ke Sistem</h2>
                        <p>Gunakan akun yang telah dibuat oleh administrator sekolah.</p>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="post" action="{{ route('login.store') }}" class="production-login-form">
                    @csrf

                    <label>
                        <span>Username / NIS</span>

                        <input
                            id="login-username"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Masukkan username atau NIS"
                        >
                    </label>

                    <label>
                        <span>Password</span>

                        <div class="production-password-field">
                            <input
                                id="login-password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan password"
                            >

                            <button type="button" data-toggle-password>
                                Lihat
                            </button>
                        </div>
                    </label>

                    <div class="production-login-options">
                        <label>
                            <input type="checkbox" name="remember">
                            <span>Ingat sesi login</span>
                        </label>
                    </div>

                    <button class="production-login-button" type="submit">
                        Masuk
                    </button>
                </form>

                <div class="production-login-note">
                    <strong>Catatan:</strong>
                    Admin dan guru menggunakan username. Siswa menggunakan NIS.
                </div>
            </div>

            <footer class="production-login-footer">
                © {{ date('Y') }} SMA Muhammadiyah 2 Kota Tangerang
            </footer>
        </section>
    </main>
</body>
</html>

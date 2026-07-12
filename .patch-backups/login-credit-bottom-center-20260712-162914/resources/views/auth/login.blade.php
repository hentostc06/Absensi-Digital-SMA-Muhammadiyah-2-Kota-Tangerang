<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Sistem Absensi QR</title>
    <link rel="icon" href="{{ asset('images/logo-sma-muhammadiyah-2.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="production-login-page">
    <main class="production-login-shell">
        <section class="production-login-brand" aria-label="Informasi sistem">
            <div class="production-brand-top">
                <img src="{{ asset('images/logo-sma-muhammadiyah-2.jpeg') }}" alt="Logo SMA Muhammadiyah 2 Kota Tangerang">
                <div>
                    <span>Sistem Absensi QR</span>
                    <strong>SMA Muhammadiyah 2 Kota Tangerang</strong>
                </div>
            </div>

            <div class="production-brand-content">
                <span class="production-label">Portal Internal</span>
                <h1>Sistem Absensi SMA Muhammadiyah 2 Kota Tangerang</h1>
                <p>Silakan masuk menggunakan akun yang telah diberikan oleh sekolah. Akses pengguna akan menyesuaikan peran masing-masing di dalam sistem.</p>
            </div>

            <div class="production-brand-footer">
                <span>Admin Sekolah</span>
                <span>Guru</span>
                <span>Siswa</span>
            </div>
        </section>

        <section class="production-login-panel" aria-label="Form login">
            <div class="production-login-card">
                <div class="production-login-header">
                    <img src="{{ asset('images/logo-sma-muhammadiyah-2.jpeg') }}" alt="Logo">
                    <div>
                        <h2>Masuk Sistem</h2>
                        <p>Gunakan username untuk admin/guru atau NIS untuk siswa.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="production-login-form">
                    @csrf

                    <label>
                        <span>Username / NIS</span>
                        <input type="text" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus placeholder="Contoh: 20260001">
                    </label>

                    <label>
                        <span>Password</span>
                        <div class="production-password-field">
                            <input type="password" name="password" autocomplete="current-password" required placeholder="Masukkan password">
                            <button type="button" data-toggle-password>Lihat</button>
                        </div>
                    </label>

                    <div class="production-login-options">
                        <label class="production-remember">
                            <input type="checkbox" name="remember" value="1">
                            <span>Ingat saya</span>
                        </label>

                        <small>Akses sesuai role akun</small>
                    </div>

                    <button class="production-login-submit" type="submit">Masuk ke Dashboard</button>
                </form>

                <p class="production-login-note">Skripsi Harnel Aikal Fairuz - 2026</p>
            </div>
        </section>
    </main>
</body>
</html>

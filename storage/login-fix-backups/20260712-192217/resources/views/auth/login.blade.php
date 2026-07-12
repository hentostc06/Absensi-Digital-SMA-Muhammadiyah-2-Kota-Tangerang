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
    <main class="auth-shell">
        <section class="auth-visual">
            <div class="orb orb-one"></div>
            <div class="orb orb-two"></div>
            <img src="{{ asset('images/logo-sekolah.svg') }}" class="auth-logo" alt="Logo">
            <span class="auth-kicker">QR CODE DINAMIS</span>
            <h1>Absensi siswa lebih cepat, aman, dan real-time.</h1>
            <p>Akun tidak dibuat bebas oleh pengguna. Admin/Tata Usaha mengatur akun guru, siswa, kelas, jadwal, dan laporan.</p>
            <div class="auth-feature-grid">
                <div><strong>30 Detik</strong><span>QR otomatis berubah</span></div>
                <div><strong>3 Role</strong><span>Admin, Guru, Siswa</span></div>
                <div><strong>Real-time</strong><span>Monitoring kelas</span></div>
            </div>
        </section>

        <section class="login-card neo-card">
            <div class="brand center">
                <div class="brand-mark">M2</div>
                <div>
                    <h1>Absensi Digital</h1>
                    <p>SMA Muhammadiyah 2 Kota Tangerang</p>
                </div>
            </div>

            <div class="auth-tabs" data-auth-tabs>
                <button type="button" class="active" data-demo-user="admin" data-demo-password="Admin123!">Admin</button>
                <button type="button" data-demo-user="guru.demo" data-demo-password="Guru123!">Guru</button>
                <button type="button" data-demo-user="19221273" data-demo-password="Siswa123!">Siswa</button>
            </div>

            <h2>Masuk ke Sistem</h2>
            <p class="muted">Gunakan akun resmi yang dibuat oleh Admin.</p>

            @if($errors->any()) <div class="alert danger">{{ $errors->first() }}</div> @endif

            <form method="post" action="{{ route('login.store') }}" class="form auth-form">
                @csrf
                <label>Username <input id="login-username" name="username" value="{{ old('username') }}" required autofocus></label>
                <label>Password <div class="password-field"><input id="login-password" type="password" name="password" required><button type="button" data-toggle-password>lihat</button></div></label>
                <label class="check"><input type="checkbox" name="remember"> Ingat saya</label>
                <button class="btn primary wide auth-submit"><span>Masuk</span><i>→</i></button>
            </form>

            <div class="demo smart-demo">
                <strong>Akun dikelola Admin</strong>
                <small>Klik tab Admin/Guru/Siswa untuk mengisi akun demo otomatis.</small>
            </div>
        </section>
    </main>
</body>
</html>

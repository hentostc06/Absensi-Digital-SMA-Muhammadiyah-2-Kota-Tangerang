@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
@php
    $user = $user ?? auth()->user();

    $teacher = method_exists($user, 'teacher') ? $user->teacher : null;
    $student = method_exists($user, 'student') ? $user->student : null;

    $phone = $user->phone
        ?? $teacher->phone
        ?? $teacher->phone_number
        ?? $teacher->no_hp
        ?? $student->phone
        ?? $student->phone_number
        ?? $student->no_hp
        ?? '-';

    $genderValue = $teacher->gender
        ?? $student->gender
        ?? $user->gender
        ?? null;

    $genderLabel = match ($genderValue) {
        'L', 'laki-laki' => 'Laki-laki',
        'P', 'perempuan' => 'Perempuan',
        default => 'Belum diatur',
    };
@endphp

<section class="account-page-head">
    <span class="section-kicker">AKUN PENGGUNA</span>
    <h1>Pengaturan Akun</h1>
    <p>Lihat data akun dan ubah password secara mandiri.</p>
</section>

@if (session('success'))
    <div class="account-alert success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="account-alert error">
        <strong>Periksa kembali data yang diisi.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="account-settings-grid">
    <article class="account-card">
        <header class="account-card-header">
            <div class="account-card-icon">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
            <div>
                <h2>Data Diri</h2>
                <p>Informasi akun yang tersimpan pada sistem.</p>
            </div>
        </header>

        <div class="account-info-list">
            <div class="account-info-row">
                <span>Nama</span>
                <strong>{{ $user->name }}</strong>
            </div>

            <div class="account-info-row">
                <span>Username / NIS</span>
                <strong>{{ $user->username }}</strong>
            </div>

            <div class="account-info-row">
                <span>Email</span>
                <strong>{{ $user->email ?? '-' }}</strong>
            </div>

            <div class="account-info-row">
                <span>Jenis Kelamin</span>
                <strong>{{ $genderLabel }}</strong>
            </div>

            <div class="account-info-row">
                <span>Role</span>
                <strong>{{ strtoupper($user->role) }}</strong>
            </div>

            <div class="account-info-row">
                <span>Status Akun</span>
                <strong>{{ $user->is_active ? 'Aktif' : 'Tidak aktif' }}</strong>
            </div>

            <div class="account-info-row">
                <span>No. HP</span>
                <strong>{{ $phone }}</strong>
            </div>
        </div>

        <div class="account-readonly-note">
            Hubungi admin atau kantor Tata Usaha untuk mengubah data diri Anda pada website ini.
        </div>
    </article>

    <article class="account-card">
        <header class="account-card-header">
            <div class="account-card-icon lock-icon">🔐</div>
            <div>
                <h2>Ganti Password</h2>
                <p>Gunakan password baru minimal 8 karakter.</p>
            </div>
        </header>

        <form method="POST" action="{{ route('account.settings.password') }}" class="account-password-form">
            @csrf

            <label>
                <span>Password Lama</span>
                <input type="password" name="current_password" autocomplete="current-password">
            </label>

            <label>
                <span>Password Baru</span>
                <input type="password" name="password" autocomplete="new-password">
            </label>

            <label>
                <span>Konfirmasi Password Baru</span>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </label>

            <button type="submit">Simpan Password Baru</button>
        </form>
    </article>
</section>
@endsection

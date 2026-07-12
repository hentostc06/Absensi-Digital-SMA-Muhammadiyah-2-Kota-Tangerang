@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
    <div class="account-settings-page">
        <div class="page-heading account-settings-heading">
            <div>
                <span class="section-kicker">Akun Pengguna</span>
                <h1>Pengaturan Akun</h1>
                <p>Lihat data akun dan ubah password secara mandiri.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        <div class="account-settings-grid">
            <section class="account-settings-card">
                <div class="account-settings-card-head">
                    <div class="account-avatar-large">
                        {{ strtoupper(substr($user->name ?? $user->username ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <h2>Data Diri</h2>
                        <p>Informasi akun yang tersimpan pada sistem.</p>
                    </div>
                </div>

                <div class="account-info-list">
                    @foreach ($profileRows as $row)
                        <div class="account-info-row">
                            <span>{{ $row['label'] }}</span>
                            <strong>{{ $row['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="account-settings-card">
                <div class="account-settings-card-head">
                    <div class="account-avatar-large lock">🔒</div>
                    <div>
                        <h2>Ganti Password</h2>
                        <p>Gunakan password baru minimal 8 karakter.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('account.settings.password') }}" class="account-password-form">
                    @csrf
                    @method('PUT')

                    <label>
                        <span>Password Lama</span>
                        <input type="password" name="current_password" required autocomplete="current-password">
                        @error('current_password')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label>
                        <span>Password Baru</span>
                        <input type="password" name="password" required autocomplete="new-password">
                        @error('password')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label>
                        <span>Konfirmasi Password Baru</span>
                        <input type="password" name="password_confirmation" required autocomplete="new-password">
                    </label>

                    <button type="submit">Simpan Password Baru</button>
                </form>
            </section>
        </div>
    </div>
@endsection

@extends('layouts.app')
@section('title', 'Kelola Akun')
@section('content')
<div class="hero-dashboard account-hero">
    <div>
        <span class="section-kicker">ADMINISTRASI AKUN</span>
        <h1>Kelola Akun Pengguna</h1>
        <p>Admin membuat akun untuk Admin, Guru, dan Siswa. Tidak ada registrasi publik agar data tetap aman.</p>
    </div>
    <div class="mini-stat-row">
        <span>Admin <b>{{ $totalAdmin }}</b></span>
        <span>Guru <b>{{ $totalGuru }}</b></span>
        <span>Siswa <b>{{ $totalSiswa }}</b></span>
    </div>
</div>

<div class="account-workspace" data-account-page>
    <section class="card glass-panel account-create-card">
        <span class="section-kicker">BUAT AKUN BARU</span>
        <h3>Pilih jenis akun</h3>

        <form method="POST" action="{{ route('admin.accounts.store') }}" class="form account-form" data-account-form>
            @csrf
            <div class="role-picker">
                <label class="role-option active" data-role-card="siswa"><input type="radio" name="role" value="siswa" checked><span>♙</span><strong>Siswa</strong><small>Admin pilih kelas, NIS otomatis</small></label>
                <label class="role-option" data-role-card="guru"><input type="radio" name="role" value="guru"><span>♟</span><strong>Guru</strong><small>Jadwal otomatis tampil di dashboard guru</small></label>
                <label class="role-option" data-role-card="admin"><input type="radio" name="role" value="admin"><span>◉</span><strong>Admin</strong><small>Akses penuh sistem</small></label>
            </div>

            <div class="form grid">
                <label>Nama Lengkap <input name="name" value="{{ old('name') }}" required placeholder="Contoh: Ahmad Fauzan"></label>
                <label>Username <input name="username" value="{{ old('username') }}" placeholder="Kosongkan untuk otomatis"></label>
                <label>Email <input type="email" name="email" value="{{ old('email') }}" placeholder="Opsional"></label>
                <label>Password <input name="password" type="password" placeholder="Kosongkan untuk default otomatis"></label>

                <div class="role-fields" data-fields-for="siswa">
                    <label>Kelas Siswa
                        <select name="school_class_id">
                            <option value="">Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->name }} - {{ $class->academic_year }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>Jenis Kelamin
                        <select name="gender">
                            <option value="">Pilih</option>
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </select>
                    </label>
                </div>

                <div class="role-fields hidden" data-fields-for="guru">
                    <label>Nomor Induk Guru / NBM <input name="niy_nbm" value="{{ old('niy_nbm') }}" placeholder="Kosongkan untuk otomatis"></label>
                </div>

                <label>No. HP <input name="phone" value="{{ old('phone') }}" placeholder="Opsional"></label>
                <label class="full">Alamat / Catatan <textarea name="address" placeholder="Opsional untuk siswa">{{ old('address') }}</textarea></label>
                <label class="check full"><input type="checkbox" name="is_active" value="1" checked> Akun langsung aktif</label>
            </div>

            <button class="btn primary wide" type="submit">Buat Akun Otomatis</button>
        </form>
    </section>

    <section class="card glass-panel account-list-card">
        <span class="section-kicker">DAFTAR AKUN</span>
        <h3>Data {{ strtoupper($role) }}</h3>

        <div class="account-tabs">
            <a href="{{ route('admin.accounts.index', ['role' => 'siswa']) }}" class="{{ $role === 'siswa' ? 'active' : '' }}">Siswa</a>
            <a href="{{ route('admin.accounts.index', ['role' => 'guru']) }}" class="{{ $role === 'guru' ? 'active' : '' }}">Guru</a>
            <a href="{{ route('admin.accounts.index', ['role' => 'admin']) }}" class="{{ $role === 'admin' ? 'active' : '' }}">Admin</a>
        </div>

        <form method="GET" action="{{ route('admin.accounts.index') }}" class="search-bar">
            <input type="hidden" name="role" value="{{ $role }}">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama, username, atau email...">
            <button class="btn">Cari</button>
        </form>

        <div class="account-list">
            @forelse ($accounts as $account)
                <div class="account-row">
                    <div class="account-main">
                        <div class="account-avatar">{{ strtoupper(substr($account->name, 0, 1)) }}</div>
                        <div>
                            <strong>{{ $account->name }}</strong>
                            <span>
                                {{ $account->username }}
                                @if ($account->role === 'siswa' && $account->student)
                                    • {{ $account->student->schoolClass->name }}
                                @elseif ($account->role === 'guru' && $account->teacher)
                                    • {{ $account->teacher->niy_nbm }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <span class="status-chip {{ $account->is_active ? 'active' : 'inactive' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span>

                    <div class="account-actions">
                        <a class="btn sm" href="{{ route('admin.accounts.edit', $account) }}">Edit</a>

                        <form method="POST" action="{{ route('admin.accounts.reset-password', $account) }}">@csrf @method('PATCH')<button class="btn sm" type="submit">Reset</button></form>
                        <form method="POST" action="{{ route('admin.accounts.toggle', $account) }}">@csrf @method('PATCH')<button class="btn sm" type="submit">{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                        <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" onsubmit="return confirm('Hapus akun ini?')">@csrf @method('DELETE')<button class="btn sm danger" type="submit">Hapus</button></form>
                    </div>
                </div>
            @empty
                <div class="empty-state"><span>⌁</span><strong>Belum ada akun</strong><p>Buat akun melalui panel di sebelah kiri.</p></div>
            @endforelse
        </div>

        {{ $accounts->links() }}
    </section>
</div>
@endsection

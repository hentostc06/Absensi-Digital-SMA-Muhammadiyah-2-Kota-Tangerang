@extends('layouts.app')
@section('title', 'Edit Akun')
@section('content')
<div class="page-head">
    <div><h1>Edit Akun</h1><p>Perbarui data akun {{ strtoupper($account->role) }}.</p></div>
    <a href="{{ route('admin.accounts.index', ['role' => $account->role]) }}" class="btn">Kembali</a>
</div>

<div class="card glass-panel">
    <form method="POST" action="{{ route('admin.accounts.update', $account) }}" class="form grid">
        @csrf
        @method('PUT')

        <label>Role <input value="{{ strtoupper($account->role) }}" disabled></label>
        <label>Status
            <select name="is_active">
                <option value="1" @selected($account->is_active)>Aktif</option>
                <option value="0" @selected(! $account->is_active)>Nonaktif</option>
            </select>
        </label>

        <label>Nama Lengkap <input name="name" value="{{ old('name', $account->name) }}" required></label>
        <label>Username <input name="username" value="{{ old('username', $account->username) }}" required></label>
        <label>Email <input type="email" name="email" value="{{ old('email', $account->email) }}"></label>
        <label>Password Baru <input type="password" name="password" placeholder="Kosongkan jika tidak diganti"></label>

        @if ($account->role === 'guru')
            <label>Nomor Induk Guru / NBM <input name="niy_nbm" value="{{ old('niy_nbm', $account->teacher?->niy_nbm) }}"></label>
        @endif

        @if ($account->role === 'siswa')
            <label>Kelas
                <select name="school_class_id">
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('school_class_id', $account->student?->school_class_id) == $class->id)>{{ $class->name }} - {{ $class->academic_year }}</option>
                    @endforeach
                </select>
            </label>

            <label>Jenis Kelamin
                <select name="gender">
                    <option value="">Pilih</option>
                    <option value="L" @selected(old('gender', $account->student?->gender) === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('gender', $account->student?->gender) === 'P')>Perempuan</option>
                </select>
            </label>
        @endif

        <label>No. HP <input name="phone" value="{{ old('phone', $account->teacher?->phone ?? $account->student?->phone) }}"></label>

        @if ($account->role === 'siswa')
            <label class="full">Alamat / Catatan <textarea name="address">{{ old('address', $account->student?->address) }}</textarea></label>
        @endif

        <div class="form-actions full">
            <button class="btn primary">Simpan Perubahan</button>
            <a href="{{ route('admin.accounts.index', ['role' => $account->role]) }}" class="btn">Batal</a>
        </div>
    </form>
</div>
@endsection

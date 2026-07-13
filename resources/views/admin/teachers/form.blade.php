@extends('layouts.app')

@section('title', $teacher->exists ? 'Edit Guru' : 'Tambah Guru')

@section('content')
<section class="page-head">
    <div>
        <span class="section-kicker">DATA GURU</span>
        <h1>{{ $teacher->exists ? 'Edit Guru' : 'Tambah Guru' }}</h1>
        <p>Kelola identitas, akun, dan status guru.</p>
    </div>
</section>

<form class="card form grid teacher-clean-form"
      method="POST"
      action="{{ $teacher->exists ? route('admin.teachers.update', $teacher) : route('admin.teachers.store') }}">
    @csrf

    @if ($teacher->exists)
        @method('PUT')
    @endif

    <label>
        Nama Lengkap
        <input name="name" value="{{ old('name', $teacher->user->name ?? '') }}" required>
    </label>

    <label>
        NIY/NBM
        <input name="niy_nbm" value="{{ old('niy_nbm', $teacher->niy_nbm) }}" required>
    </label>

    <label>
        Username
        <input name="username" value="{{ old('username', $teacher->user->username ?? '') }}" required>
    </label>

    <label>
        No. Telepon
        <input name="phone" value="{{ old('phone', $teacher->phone) }}">
    </label>

    <label>
        Jenis Kelamin
        <select name="gender">
            <option value="">Pilih jenis kelamin</option>
            <option value="L" @selected(old('gender', $teacher->gender) === 'L')>Laki-laki</option>
            <option value="P" @selected(old('gender', $teacher->gender) === 'P')>Perempuan</option>
        </select>
    </label>

    @if ($teacher->exists)
        <label class="teacher-active-box">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $teacher->user->is_active))>
            <span>Akun aktif</span>
        </label>
    @else
        <div></div>
    @endif

    <label>
        Password {{ $teacher->exists ? '(opsional)' : '' }}
        <input type="password" name="password" {{ $teacher->exists ? '' : 'required' }}>
    </label>

    <label>
        Konfirmasi Password
        <input type="password" name="password_confirmation" {{ $teacher->exists ? '' : 'required' }}>
    </label>

    <div class="actions full teacher-form-actions">
        <button class="btn primary" type="submit">Simpan</button>
        <a class="btn" href="{{ route('admin.teachers.index') }}">Batal</a>
    </div>
</form>

<style>
    .teacher-clean-form {
        align-items: end !important;
    }

    .teacher-clean-form label {
        display: grid !important;
        gap: 8px !important;
        margin: 0 !important;
    }

    .teacher-clean-form input,
    .teacher-clean-form select {
        margin-top: 0 !important;
    }

    .teacher-active-box {
        min-height: 46px !important;
        padding: 0 14px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        border: 1px solid #dbe5f1 !important;
        border-radius: 13px !important;
        background: #f8fbff !important;
    }

    .teacher-active-box input[type="checkbox"] {
        width: 16px !important;
        height: 16px !important;
        min-height: 16px !important;
        margin: 0 !important;
        padding: 0 !important;
        flex: 0 0 auto !important;
    }

    .teacher-active-box span {
        color: #082455 !important;
        font-size: 13px !important;
        font-weight: 900 !important;
    }

    .teacher-form-actions {
        margin-top: 4px !important;
        justify-content: flex-start !important;
    }
</style>
@endsection

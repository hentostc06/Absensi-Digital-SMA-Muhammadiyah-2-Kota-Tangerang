@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
<section class="admin-list-hero">
    <div>
        <span class="section-kicker">MASTER DATA</span>
        <h1>Data Guru</h1>
        <p>Klik NIY/NBM atau nama guru untuk melihat mata pelajaran, kelas, dan jadwal mengajarnya.</p>
    </div>

    <a class="admin-add-button" href="{{ route('admin.teachers.create') }}">+ Tambah Guru</a>
</section>

<section class="admin-search-card">
    <form method="GET" action="{{ route('admin.teachers.index') }}" class="admin-search-form">
        <label class="admin-search-field">
            <span>Pencarian guru</span>
            <div class="admin-search-input">
                <b>NBM</b>
                <input name="q" value="{{ request('q') }}" placeholder="Cari NIY/NBM atau nama guru...">
            </div>
        </label>

        <button class="admin-search-submit" type="submit">Cari Data</button>

        @if (request('q'))
            <a class="admin-search-reset" href="{{ route('admin.teachers.index') }}">Reset</a>
        @endif
    </form>
</section>

<section class="card table-wrap admin-table-card">
    <table>
        <thead>
        <tr>
            <th>NIY/NBM</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($items as $x)
            <tr class="clickable-table-row">
                <td>
                    <a class="teacher-main-link" href="{{ route('admin.teachers.show', $x) }}">
                        {{ $x->niy_nbm }}
                    </a>
                </td>

                <td>
                    <a class="teacher-title-link" href="{{ route('admin.teachers.show', $x) }}">
                        {{ $x->user->name }}
                        <small>Lihat jadwal mengajar</small>
                    </a>
                </td>

                <td>{{ $x->user->username }}</td>

                <td>
                    <span class="badge {{ $x->user->is_active ? 'green' : 'red' }}">
                        {{ $x->user->is_active ? 'Aktif' : 'Terkunci' }}
                    </span>
                </td>

                <td class="actions">
                    <a class="btn sm" href="{{ route('admin.teachers.show', $x) }}">Detail</a>
                    <a class="btn sm" href="{{ route('admin.teachers.edit', $x) }}">Edit</a>

                    <form method="POST" action="{{ route('admin.teachers.destroy', $x) }}" data-confirm="Hapus guru ini?">
                        @csrf
                        @method('delete')
                        <button class="btn sm danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="empty">Belum ada data guru.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</section>

<div class="admin-pagination-wrap">
    {{ $items->links() }}
</div>
@endsection

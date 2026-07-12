@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<section class="admin-list-hero">
    <div>
        <span class="section-kicker">MASTER DATA</span>
        <h1>Data Siswa</h1>
        <p>Kelola akun, identitas, kelas, dan status akses siswa.</p>
    </div>

    <a class="admin-add-button" href="{{ route('admin.students.create') }}">+ Tambah Siswa</a>
</section>

<section class="admin-search-card">
    <form method="GET" action="{{ route('admin.students.index') }}" class="admin-search-form">
        <label class="admin-search-field">
            <span>Pencarian siswa</span>
            <div class="admin-search-input">
                <b>NIS</b>
                <input name="q" value="{{ request('q') }}" placeholder="Cari NIS atau nama siswa...">
            </div>
        </label>

        <button class="admin-search-submit" type="submit">Cari Data</button>

        @if (request('q'))
            <a class="admin-search-reset" href="{{ route('admin.students.index') }}">Reset</a>
        @endif
    </form>
</section>

<section class="card table-wrap admin-table-card">
    <table>
        <thead>
        <tr>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Status Akun</th>
            <th>Aksi</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($items as $x)
            <tr>
                <td><strong>{{ $x->nis }}</strong></td>
                <td>{{ $x->user->name }}</td>
                <td>{{ $x->schoolClass->name }}</td>
                <td>
                    <span class="badge {{ $x->user->is_active ? 'green' : 'red' }}">
                        {{ $x->user->is_active ? 'Aktif' : 'Terkunci' }}
                    </span>
                </td>
                <td class="actions">
                    <a class="btn sm" href="{{ route('admin.students.edit', $x) }}">Edit</a>

                    <form method="POST" action="{{ route('admin.students.destroy', $x) }}" data-confirm="Hapus siswa ini?">
                        @csrf
                        @method('delete')
                        <button class="btn sm danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="empty">Belum ada data siswa.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</section>

<div class="admin-pagination-wrap">
    {{ $items->links() }}
</div>
@endsection

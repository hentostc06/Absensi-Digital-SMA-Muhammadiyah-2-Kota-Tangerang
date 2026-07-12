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
    <form method="GET" action="{{ route('admin.students.index') }}" class="admin-search-form admin-student-filter-form">
        <label class="admin-search-field">
            <span>Pencarian siswa</span>
            <div class="admin-search-input">
                <b>NIS</b>
                <input name="q" value="{{ $search ?? request('q') }}" placeholder="Cari NIS atau nama siswa...">
            </div>
        </label>

        <label class="admin-search-field admin-class-filter-field">
            <span>Filter kelas</span>
            <div class="admin-search-input admin-select-input">
                <b>Kelas</b>
                <select name="class_id">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((string) ($selectedClass ?? request('class_id')) === (string) $class->id)>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </label>

        <button class="admin-search-submit" type="submit">Terapkan</button>

        @if (request('q') || request('class_id'))
            <a class="admin-search-reset" href="{{ route('admin.students.index') }}">Reset</a>
        @endif
    </form>

    @if (request('q') || request('class_id'))
        <div class="admin-active-filter">
            <span>Filter aktif:</span>

            @if (request('q'))
                <b>Pencarian: {{ request('q') }}</b>
            @endif

            @if (request('class_id'))
                <b>Kelas: {{ $classes->firstWhere('id', (int) request('class_id'))?->name ?? 'Kelas dipilih' }}</b>
            @endif

            <small>{{ $items->total() }} siswa ditemukan</small>
        </div>
    @endif
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
                <td>{{ $x->user->name ?? '-' }}</td>
                <td>{{ $x->schoolClass->name ?? '-' }}</td>
                <td>
                    <span class="badge {{ ($x->user->is_active ?? false) ? 'green' : 'red' }}">
                        {{ ($x->user->is_active ?? false) ? 'Aktif' : 'Terkunci' }}
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
                <td colspan="5" class="empty">
                    @if (request('q') || request('class_id'))
                        Tidak ada siswa yang sesuai dengan filter.
                    @else
                        Belum ada data siswa.
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</section>

<div class="admin-pagination-wrap">
    {{ $items->links() }}
</div>
@endsection

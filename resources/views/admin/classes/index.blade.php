@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<section class="admin-list-hero">
    <div>
        <span class="section-kicker">MASTER DATA</span>
        <h1>Data Kelas</h1>
        <p>Klik kode atau nama kelas untuk melihat daftar siswa dan jadwal pelajaran kelas tersebut.</p>
    </div>

    <a class="admin-add-button" href="{{ route('admin.classes.create') }}">+ Tambah Kelas</a>
</section>

<section class="card table-wrap admin-table-card">
    <table>
        <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Kelas</th>
            <th>Tingkat</th>
            <th>Tahun Ajaran</th>
            <th>Aksi</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($items as $x)
            <tr class="clickable-table-row">
                <td>
                    <a class="class-main-link" href="{{ route('admin.classes.show', $x) }}">
                        {{ $x->code }}
                    </a>
                </td>
                <td>
                    <a class="class-title-link" href="{{ route('admin.classes.show', $x) }}">
                        {{ $x->name }}
                        <small>Lihat siswa dan jadwal</small>
                    </a>
                </td>
                <td>{{ $x->grade }}</td>
                <td>{{ $x->academic_year }}</td>
                <td class="actions">
                    <a class="btn sm" href="{{ route('admin.classes.show', $x) }}">Detail</a>
                    <a class="btn sm" href="{{ route('admin.classes.edit', $x) }}">Edit</a>

                    <form method="POST" action="{{ route('admin.classes.destroy', $x) }}" data-confirm="Hapus kelas ini?">
                        @csrf
                        @method('delete')
                        <button class="btn sm danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="empty">Belum ada data kelas.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</section>

<div class="admin-pagination-wrap">
    {{ $items->links() }}
</div>
@endsection

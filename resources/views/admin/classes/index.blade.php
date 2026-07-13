@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<section class="admin-list-hero classes-page-hero">
    <div>
        <span class="section-kicker">MASTER DATA</span>
        <h1>Data Kelas</h1>
        <p>Klik kode atau nama kelas untuk melihat daftar siswa dan jadwal pelajaran kelas tersebut.</p>
    </div>

    <a class="admin-add-button" href="{{ route('admin.classes.create') }}">+ Tambah Kelas</a>
</section>

<section class="card table-wrap admin-table-card classes-table-card">
    <table class="classes-table">
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
                <tr>
                    <td>
                        <a class="class-code-badge" href="{{ route('admin.classes.show', $x) }}">
                            {{ $x->code }}
                        </a>
                    </td>

                    <td>
                        <div class="class-name-cell">
                            <a class="class-name-link" href="{{ route('admin.classes.show', $x) }}">
                                {{ $x->name }}
                            </a>

                            <a class="class-sub-link" href="{{ route('admin.classes.show', $x) }}">
                                Lihat siswa dan jadwal
                            </a>
                        </div>
                    </td>

                    <td>
                        <span class="class-grade-text">{{ $x->grade }}</span>
                    </td>

                    <td>
                        <span class="class-year-text">{{ $x->academic_year }}</span>
                    </td>

                    <td>
                        <div class="class-action-group">
                            <a class="btn sm" href="{{ route('admin.classes.show', $x) }}">Detail</a>
                            <a class="btn sm" href="{{ route('admin.classes.edit', $x) }}">Edit</a>

                            <form method="POST" action="{{ route('admin.classes.destroy', $x) }}" data-confirm="Hapus kelas ini?">
                                @csrf
                                @method('delete')
                                <button class="btn sm danger" type="submit">Hapus</button>
                            </form>
                        </div>
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

<style>
    .classes-table-card {
        padding: 18px !important;
        overflow-x: auto !important;
    }

    .classes-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .classes-table th {
        padding: 16px 18px !important;
        background: #eaf2ff !important;
        color: #082455 !important;
        font-size: 12px !important;
        font-weight: 950 !important;
        letter-spacing: .08em !important;
        text-transform: uppercase !important;
        text-align: left !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    .classes-table td {
        padding: 16px 18px !important;
        border-bottom: 1px solid #dbe5f1 !important;
        background: #ffffff !important;
        color: #082455 !important;
        vertical-align: middle !important;
    }

    .classes-table tbody tr:hover td {
        background: #f8fbff !important;
    }

    .class-code-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 58px !important;
        min-height: 36px !important;
        padding: 0 12px !important;
        border: 1px solid #dbe5f1 !important;
        border-radius: 12px !important;
        background: #f8fbff !important;
        color: #082455 !important;
        font-size: 14px !important;
        font-weight: 950 !important;
        text-decoration: none !important;
    }

    .class-code-badge:hover {
        border-color: #f6c344 !important;
        background: #fffaf0 !important;
    }

    .class-name-cell {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 4px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .class-name-link {
        display: inline-block !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        background: transparent !important;
        color: #082455 !important;
        font-size: 15px !important;
        line-height: 1.25 !important;
        font-weight: 950 !important;
        text-decoration: none !important;
        box-shadow: none !important;
    }

    .class-sub-link {
        display: inline-block !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        background: transparent !important;
        color: #53657f !important;
        font-size: 12px !important;
        line-height: 1.35 !important;
        font-weight: 850 !important;
        text-decoration: none !important;
        box-shadow: none !important;
    }

    .class-name-link:hover,
    .class-sub-link:hover {
        color: #0f4c9c !important;
        text-decoration: underline !important;
    }

    .class-grade-text,
    .class-year-text {
        color: #082455 !important;
        font-weight: 850 !important;
        white-space: nowrap !important;
    }

    .class-action-group {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
    }

    .class-action-group form {
        display: inline-flex !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .class-action-group .btn {
        min-height: 38px !important;
        padding: 0 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 12px !important;
        font-weight: 950 !important;
        line-height: 1 !important;
    }

    @media (max-width: 900px) {
        .classes-table {
            min-width: 820px !important;
        }
    }
</style>

<!-- BADCODING_CLASS_NAME_NO_BOX_FINAL -->
<style>
    /* Final rapihin kolom Nama Kelas: hilangkan efek kotak/link kecil */
    .classes-table .class-name-cell {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        justify-content: center !important;
        gap: 5px !important;
        min-height: 44px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        outline: 0 !important;
    }

    .classes-table .class-name-link,
    .classes-table .class-sub-link {
        all: unset !important;
        display: block !important;
        cursor: pointer !important;
        box-sizing: border-box !important;
    }

    .classes-table .class-name-link {
        color: #082455 !important;
        font-size: 15px !important;
        line-height: 1.25 !important;
        font-weight: 950 !important;
    }

    .classes-table .class-sub-link {
        color: #64748b !important;
        font-size: 12px !important;
        line-height: 1.35 !important;
        font-weight: 800 !important;
    }

    .classes-table .class-name-cell:hover .class-name-link {
        color: #0f4c9c !important;
    }

    .classes-table .class-name-cell:hover .class-sub-link {
        color: #315b96 !important;
    }

    .classes-table td:nth-child(2) {
        min-width: 320px !important;
    }

    .classes-table td:nth-child(2) * {
        text-decoration: none !important;
        border: 0 !important;
        outline: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .classes-table th,
    .classes-table td {
        text-align: left !important;
    }

    .classes-table td:nth-child(3),
    .classes-table td:nth-child(4) {
        font-weight: 900 !important;
    }
</style>

@endsection

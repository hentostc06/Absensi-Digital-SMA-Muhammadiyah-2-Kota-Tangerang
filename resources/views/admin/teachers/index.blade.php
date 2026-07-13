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

<!-- BADCODING_TEACHER_LIST_TABLE_CLEAN_UI -->
<style>
    /*
     * Khusus halaman Data Guru.
     * Fokus: hilangkan kotak aneh di kolom nama, rapikan row, badge, dan tombol.
     */

    table {
        width: 100%;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    table thead th {
        padding: 16px 18px !important;
        color: #082455 !important;
        background: #eaf2ff !important;
        border: 0 !important;
        font-size: 12px !important;
        line-height: 1.2 !important;
        letter-spacing: .08em !important;
        text-transform: uppercase !important;
        font-weight: 950 !important;
        white-space: nowrap !important;
    }

    table tbody td {
        padding: 16px 18px !important;
        border-bottom: 1px solid #dbe5f1 !important;
        color: #082455 !important;
        vertical-align: middle !important;
        background: #ffffff !important;
        font-size: 14px !important;
        line-height: 1.45 !important;
        font-weight: 750 !important;
    }

    table tbody tr:hover td {
        background: #f8fbff !important;
    }

    table tbody td:nth-child(1) {
        width: 150px !important;
        white-space: nowrap !important;
    }

    table tbody td:nth-child(2) {
        min-width: 280px !important;
    }

    table tbody td:nth-child(3) {
        min-width: 150px !important;
        white-space: nowrap !important;
    }

    table tbody td:nth-child(4) {
        width: 120px !important;
        white-space: nowrap !important;
    }

    table tbody td:nth-child(5) {
        width: 250px !important;
        white-space: nowrap !important;
    }

    /* NBM badge */
    table tbody td:nth-child(1) :is(span, b, strong, a) {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 36px !important;
        padding: 0 12px !important;
        border: 1px solid #dbe5f1 !important;
        border-radius: 12px !important;
        color: #082455 !important;
        background: #f8fbff !important;
        text-decoration: none !important;
        font-weight: 950 !important;
        box-shadow: none !important;
    }

    /* Kolom nama: jangan ada kotak seperti input */
    table tbody td:nth-child(2),
    table tbody td:nth-child(2) * {
        box-shadow: none !important;
    }

    table tbody td:nth-child(2) :is(a, span, div, strong, b) {
        border: 0 !important;
        background: transparent !important;
    }

    table tbody td:nth-child(2) > a,
    table tbody td:nth-child(2) > div,
    table tbody td:nth-child(2) > span {
        display: grid !important;
        gap: 4px !important;
        width: auto !important;
        min-height: auto !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }

    table tbody td:nth-child(2) strong,
    table tbody td:nth-child(2) b,
    table tbody td:nth-child(2) a:first-child {
        color: #082455 !important;
        font-size: 14px !important;
        line-height: 1.25 !important;
        font-weight: 950 !important;
        text-decoration: none !important;
    }

    table tbody td:nth-child(2) a[href*="schedule"],
    table tbody td:nth-child(2) a[href*="jadwal"],
    table tbody td:nth-child(2) small,
    table tbody td:nth-child(2) p {
        display: inline-flex !important;
        width: fit-content !important;
        margin: 3px 0 0 !important;
        padding: 0 !important;
        border: 0 !important;
        color: #082455 !important;
        background: transparent !important;
        font-size: 12px !important;
        line-height: 1.35 !important;
        font-weight: 850 !important;
        text-decoration: none !important;
        opacity: .9 !important;
    }

    table tbody td:nth-child(2) a[href*="schedule"]:hover,
    table tbody td:nth-child(2) a[href*="jadwal"]:hover {
        color: #0f4c9c !important;
        text-decoration: underline !important;
    }

    /* Status badge */
    table tbody td:nth-child(4) .badge,
    table tbody td:nth-child(4) span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 30px !important;
        padding: 0 12px !important;
        border-radius: 999px !important;
        color: #067647 !important;
        background: #ecfdf3 !important;
        font-size: 12px !important;
        font-weight: 950 !important;
        white-space: nowrap !important;
    }

    /* Tombol aksi */
    table tbody td:last-child {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
        white-space: nowrap !important;
    }

    table tbody td:last-child a,
    table tbody td:last-child button {
        min-height: 38px !important;
        padding: 0 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid #dbe5f1 !important;
        border-radius: 12px !important;
        color: #082455 !important;
        background: #ffffff !important;
        text-decoration: none !important;
        font-size: 13px !important;
        line-height: 1 !important;
        font-weight: 950 !important;
        box-shadow: none !important;
        cursor: pointer !important;
    }

    table tbody td:last-child a:hover,
    table tbody td:last-child button:hover {
        border-color: #f6c344 !important;
        background: #fffaf0 !important;
    }

    table tbody td:last-child button,
    table tbody td:last-child form button,
    table tbody td:last-child .danger,
    table tbody td:last-child a[href*="delete"] {
        color: #b42318 !important;
    }

    table tbody td:last-child form {
        display: inline-flex !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Mobile/tablet */
    @media (max-width: 900px) {
        .table-wrap,
        table {
            overflow-x: auto !important;
        }

        table {
            min-width: 900px !important;
        }
    }
</style>

@endsection

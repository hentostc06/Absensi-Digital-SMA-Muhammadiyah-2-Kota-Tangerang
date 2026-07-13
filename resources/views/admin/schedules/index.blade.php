@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
<section class="schedule-page-head">
    <div class="schedule-title-block">
        <span class="section-kicker">MASTER JADWAL</span>
        <h1>Jadwal Pelajaran</h1>
        <p>Kelola relasi guru, kelas, mata pelajaran, ruang, dan jam mengajar.</p>
    </div>

    <form method="GET" action="{{ route('admin.schedules.index') }}" class="schedule-filter-bar">
        <label>
            <span>Guru</span>
            <select name="teacher_id">
                <option value="">Semua Guru</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected((string) $selectedTeacherId === (string) $teacher->id)>
                        {{ $teacher->user->name ?? '-' }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span>Hari</span>
            <select name="day">
                <option value="">Semua Hari</option>
                @foreach ($days as $day)
                    <option value="{{ $day }}" @selected($selectedDay === $day)>
                        {{ $day }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span>Mapel</span>
            <select name="subject_id">
                <option value="">Semua Mapel</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) $selectedSubjectId === (string) $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <button class="btn primary" type="submit">Terapkan</button>

        @if ($selectedTeacherId || $selectedDay || $selectedSubjectId)
            <a class="btn" href="{{ route('admin.schedules.index') }}">Reset</a>
        @endif
    </form>

    <a class="btn primary schedule-add-button" href="{{ route('admin.schedules.create') }}">
        + Tambah Jadwal
    </a>
</section>

<section class="card table-wrap schedule-table-card">
    <table class="schedule-table">
        <thead>
            <tr>
                <th>Hari/Jam</th>
                <th>Guru</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Ruang</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($items as $x)
                <tr>
                    <td>
                        <div class="schedule-time-cell">
                            <strong>{{ $x->day_of_week }}</strong>
                            <span>{{ substr((string) $x->start_time, 0, 5) }}–{{ substr((string) $x->end_time, 0, 5) }}</span>
                        </div>
                    </td>

                    <td>{{ $x->teacher->user->name ?? '-' }}</td>
                    <td>{{ $x->schoolClass->name ?? '-' }}</td>
                    <td>{{ $x->subject->name ?? '-' }}</td>
                    <td>{{ $x->room ?: '-' }}</td>

                    <td>
                        <div class="schedule-action-group">
                            <a class="btn sm" href="{{ route('admin.schedules.edit', $x) }}">Edit</a>

                            <form method="POST" action="{{ route('admin.schedules.destroy', $x) }}" data-confirm="Hapus jadwal ini?">
                                @csrf
                                @method('delete')
                                <button class="btn sm danger" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">
                        Tidak ada jadwal sesuai filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

<div class="admin-pagination-wrap">
    {{ $items->links('vendor.pagination.badcoding') }}
</div>

<style>
    .schedule-page-head {
        display: grid;
        grid-template-columns: minmax(280px, 390px) minmax(620px, 1fr) auto;
        align-items: end;
        gap: 18px;
        margin-bottom: 22px;
    }

    .schedule-title-block {
        padding: 0;
        max-width: 390px;
    }

    .schedule-title-block .section-kicker {
        display: inline-flex;
        margin-bottom: 10px;
        letter-spacing: .13em;
    }

    .schedule-title-block h1 {
        margin: 0;
        color: #082455;
        font-size: clamp(34px, 3.2vw, 46px);
        line-height: 1.02;
        font-weight: 950;
        letter-spacing: -0.04em;
    }

    .schedule-title-block p {
        max-width: 360px;
        margin: 12px 0 0;
        color: #53657f;
        font-size: 15px;
        line-height: 1.6;
        font-weight: 750;
    }

    .schedule-filter-bar {
        display: grid;
        grid-template-columns: minmax(180px, 1.2fr) minmax(130px, .8fr) minmax(180px, 1fr) auto auto;
        gap: 10px;
        align-items: end;
        padding: 12px;
        border: 1px solid #dbe5f1;
        border-radius: 20px;
        background: rgba(255,255,255,.86);
        box-shadow: 0 16px 38px rgba(8, 36, 85, .06);
    }

    .schedule-filter-bar label {
        display: grid;
        gap: 6px;
        margin: 0;
    }

    .schedule-filter-bar label span {
        color: #082455;
        font-size: 12px;
        line-height: 1.2;
        font-weight: 950;
    }

    .schedule-filter-bar select {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #dbe5f1;
        border-radius: 13px;
        color: #082455;
        background: #ffffff;
        font-weight: 850;
        outline: none;
    }

    .schedule-filter-bar select:focus {
        border-color: #f6c344;
        box-shadow: 0 0 0 4px rgba(246, 195, 68, .18);
    }

    .schedule-filter-bar .btn,
    .schedule-add-button {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .schedule-table-card {
        padding: 18px !important;
        overflow-x: auto !important;
    }

    .schedule-table {
        width: 100%;
        border-collapse: collapse;
    }

    .schedule-table th {
        padding: 16px 18px;
        background: #eaf2ff;
        color: #082455;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .08em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .schedule-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #dbe5f1;
        color: #082455;
        background: #ffffff;
        vertical-align: middle;
        font-weight: 750;
    }

    .schedule-table tbody tr:hover td {
        background: #f8fbff;
    }

    .schedule-time-cell {
        display: grid;
        gap: 4px;
    }

    .schedule-time-cell strong {
        color: #082455;
        font-size: 14px;
        font-weight: 950;
    }

    .schedule-time-cell span {
        color: #53657f;
        font-size: 13px;
        font-weight: 850;
    }

    .schedule-action-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .schedule-action-group form {
        margin: 0;
        padding: 0;
        display: inline-flex;
    }

    .schedule-action-group .btn {
        min-height: 38px;
        padding: 0 14px;
        border-radius: 12px;
        font-weight: 950;
    }

    @media (max-width: 1250px) {
        .schedule-page-head {
            grid-template-columns: 1fr;
            align-items: stretch;
        }

        .schedule-title-block,
        .schedule-title-block p {
            max-width: none;
        }

        .schedule-filter-bar {
            grid-template-columns: 1fr 1fr 1fr auto auto;
        }

        .schedule-add-button {
            width: fit-content;
        }
    }

    @media (max-width: 820px) {
        .schedule-filter-bar {
            grid-template-columns: 1fr;
        }

        .schedule-add-button {
            width: 100%;
        }

        .schedule-table {
            min-width: 900px;
        }
    }
</style>
@endsection

@extends('layouts.app')

@section('title', 'Jadwal Saya')

@section('content')
@php
    $daysOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $groupedSchedules = collect($schedules ?? [])->groupBy('day_of_week');
@endphp

<section class="page-head">
    <div>
        <span class="section-kicker">JADWAL GURU</span>
        <h1>Jadwal Saya</h1>
        <p>Daftar jadwal mengajar yang terdaftar pada akun guru.</p>
    </div>
</section>

<div class="my-schedule-grid">
    @foreach ($daysOrder as $day)
        @php
            $items = $groupedSchedules->get($day, collect());
            $isToday = ($todayName ?? '') === $day;
        @endphp

        <article class="my-schedule-day-card {{ $isToday ? 'is-today' : '' }}">
            <header>
                <div>
                    <span>{{ $isToday ? 'Hari Ini' : 'Jadwal' }}</span>
                    <h2>{{ $day }}</h2>
                </div>

                <strong>{{ $items->count() }} jadwal</strong>
            </header>

            <div class="my-schedule-list">
                @forelse ($items as $schedule)
                    <div class="my-schedule-item">
                        <div>
                            <h3>{{ $schedule->subject->name ?? '-' }}</h3>
                            <p>{{ $schedule->schoolClass->name ?? '-' }}</p>
                        </div>

                        <span>
                            {{ substr((string) $schedule->start_time, 0, 5) }}
                            –
                            {{ substr((string) $schedule->end_time, 0, 5) }}
                        </span>
                    </div>
                @empty
                    <div class="my-schedule-empty">
                        Tidak ada jadwal mengajar.
                    </div>
                @endforelse
            </div>
        </article>
    @endforeach
</div>

<style>
    .my-schedule-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .my-schedule-day-card {
        padding: 22px;
        border: 1px solid #dbe5f1;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 18px 44px rgba(8, 36, 85, .07);
    }

    .my-schedule-day-card.is-today {
        border-color: rgba(246, 195, 68, .9);
        background: linear-gradient(135deg, #ffffff 0%, #fffaf0 100%);
    }

    .my-schedule-day-card header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .my-schedule-day-card header span {
        display: block;
        margin-bottom: 4px;
        color: #53657f;
        font-size: 12px;
        letter-spacing: .12em;
        text-transform: uppercase;
        font-weight: 950;
    }

    .my-schedule-day-card h2 {
        margin: 0;
        color: #082455;
        font-size: 24px;
        line-height: 1.15;
        font-weight: 950;
    }

    .my-schedule-day-card header strong {
        padding: 8px 12px;
        border-radius: 999px;
        color: #082455;
        background: #edf4ff;
        border: 1px solid #dbe5f1;
        font-size: 13px;
        font-weight: 950;
        white-space: nowrap;
    }

    .my-schedule-list {
        display: grid;
        gap: 10px;
    }

    .my-schedule-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 16px;
        border: 1px solid #e5edf7;
        border-radius: 16px;
        background: #f8fbff;
    }

    .my-schedule-item h3 {
        margin: 0 0 4px;
        color: #082455;
        font-size: 15px;
        font-weight: 950;
    }

    .my-schedule-item p {
        margin: 0;
        color: #53657f;
        font-size: 13px;
        font-weight: 750;
    }

    .my-schedule-item span {
        padding: 8px 11px;
        border-radius: 999px;
        color: #082455;
        background: #f6c344;
        font-size: 13px;
        font-weight: 950;
        white-space: nowrap;
    }

    .my-schedule-empty {
        padding: 14px 16px;
        border: 1px dashed #cfdced;
        border-radius: 16px;
        color: #53657f;
        background: #f8fbff;
        font-weight: 800;
    }

    @media (max-width: 900px) {
        .my-schedule-grid {
            grid-template-columns: 1fr;
        }

        .my-schedule-item {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endsection

<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class MyScheduleController extends Controller
{
    private const DAYS = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu',
    ];

    public function teacher(Request $request)
    {
        $teacher = $request->user()->teacher;

        $items = collect();

        if ($teacher) {
            $items = Schedule::with(['schoolClass', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->orderByRaw($this->dayOrderSql())
                ->orderBy('start_time')
                ->get();
        }

        return view('schedules.my-teacher', [
            'teacher' => $teacher,
            'items' => $items,
            'groupedSchedules' => $this->groupByDay($items),
            'today' => $this->todayName(),
        ]);
    }

    public function student(Request $request)
    {
        $student = $request->user()->student;

        $items = collect();

        if ($student && $student->school_class_id) {
            $items = Schedule::with(['teacher.user', 'subject', 'schoolClass'])
                ->where('school_class_id', $student->school_class_id)
                ->where('is_active', true)
                ->orderByRaw($this->dayOrderSql())
                ->orderBy('start_time')
                ->get();
        }

        return view('schedules.my-student', [
            'student' => $student,
            'items' => $items,
            'groupedSchedules' => $this->groupByDay($items),
            'today' => $this->todayName(),
        ]);
    }

    private function groupByDay($items)
    {
        return collect(self::DAYS)
            ->mapWithKeys(fn ($day) => [
                $day => $items->where('day_of_week', $day)->values(),
            ])
            ->filter(fn ($rows) => $rows->isNotEmpty());
    }

    private function todayName(): string
    {
        return self::DAYS[((int) now()->format('N')) - 1] ?? 'Senin';
    }

    private function dayOrderSql(): string
    {
        return "CASE day_of_week
            WHEN 'Senin' THEN 1
            WHEN 'Selasa' THEN 2
            WHEN 'Rabu' THEN 3
            WHEN 'Kamis' THEN 4
            WHEN 'Jumat' THEN 5
            WHEN 'Sabtu' THEN 6
            WHEN 'Minggu' THEN 7
            ELSE 8
        END";
    }
}

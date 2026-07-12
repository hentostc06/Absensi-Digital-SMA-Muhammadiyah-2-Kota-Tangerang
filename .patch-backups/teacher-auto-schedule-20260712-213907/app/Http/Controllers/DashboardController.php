<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = request()->user();

        if ($user->role === 'admin') {
            return view('dashboard.admin', [
                'students' => Student::count(),
                'teachers' => Teacher::count(),
                'classes' => SchoolClass::count(),
                'accounts' => User::count(),
                'today' => Attendance::whereDate('scanned_at', today())->count(),
                'lateToday' => Attendance::whereDate('scanned_at', today())->where('status', 'terlambat')->count(),
                'openSessions' => AttendanceSession::where('status', 'open')->count(),
                'recentAccounts' => User::latest()->limit(6)->get(),
            ]);
        }

        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            $day = $this->todayName();
            $time = now()->format('H:i:s');

            $currentSchedule = $teacher
                ? Schedule::with(['schoolClass', 'subject'])
                    ->where('teacher_id', $teacher->id)
                    ->where('day_of_week', $day)
                    ->where('start_time', '<=', $time)
                    ->where('end_time', '>=', $time)
                    ->where('is_active', true)
                    ->first()
                : null;

            $todaySchedules = $teacher
                ? Schedule::with(['schoolClass', 'subject'])
                    ->where('teacher_id', $teacher->id)
                    ->where('day_of_week', $day)
                    ->where('is_active', true)
                    ->orderBy('start_time')
                    ->get()
                : collect();

            $openSession = $teacher
                ? AttendanceSession::with(['schoolClass', 'subject'])
                    ->where('teacher_id', $teacher->id)
                    ->where('status', 'open')
                    ->latest()
                    ->first()
                : null;

            return view('dashboard.teacher', compact('teacher', 'day', 'currentSchedule', 'todaySchedules', 'openSession'));
        }

        $student = $user->student?->load('schoolClass');

        return view('dashboard.student', [
            'student' => $student,
            'todayAttendance' => $student
                ? Attendance::with('session.subject')->where('student_id', $student->id)->whereDate('created_at', today())->latest()->get()
                : collect(),
            'recent' => $student
                ? $student->attendances()->with(['session.subject', 'session.schoolClass'])->latest()->limit(8)->get()
                : collect(),
        ]);
    }

    private function todayName(): string
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][(int) now()->format('N')];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();

        if ($user->role === 'admin') {
            return view('dashboard.admin', [
                'students' => Student::count(),
                'teachers' => Teacher::count(),
                'classes' => SchoolClass::count(),
                'accounts' => User::count(),
                'today' => Attendance::whereDate('scanned_at', today())->count(),
                'lateToday' => Attendance::whereDate('scanned_at', today())
                    ->where('status', 'terlambat')
                    ->count(),
                'openSessions' => AttendanceSession::where('status', 'open')->count(),
                'recentAccounts' => User::latest()->limit(6)->get(),
            ]);
        }

        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            $day = $this->todayName();
            $now = now();
            $time = $now->format('H:i:s');

            $todaySchedules = $teacher
                ? Schedule::with(['schoolClass', 'subject'])
                    ->where('teacher_id', $teacher->id)
                    ->where('day_of_week', $day)
                    ->where('is_active', true)
                    ->orderBy('start_time')
                    ->get()
                : collect();

            $currentSchedule = $todaySchedules->first(function ($schedule) use ($time): bool {
                return $schedule->start_time <= $time && $schedule->end_time >= $time;
            });

            $nextSchedule = $todaySchedules->first(function ($schedule) use ($time): bool {
                return $schedule->start_time > $time;
            });

            $suggestedSchedule = $currentSchedule ?: $nextSchedule;
            $scheduleStatus = $currentSchedule
                ? 'Sedang berlangsung'
                : ($nextSchedule ? 'Jadwal berikutnya' : 'Tidak ada jadwal aktif hari ini');

            $openSession = $teacher
                ? AttendanceSession::with(['schoolClass', 'subject'])
                    ->where('teacher_id', $teacher->id)
                    ->where('status', 'open')
                    ->latest('opened_at')
                    ->first()
                : null;

            return view('dashboard.teacher', [
                'teacher' => $teacher,
                'teacherTitle' => $this->teacherTitle($teacher),
                'day' => $day,
                'todaySchedules' => $todaySchedules,
                'currentSchedule' => $currentSchedule,
                'nextSchedule' => $nextSchedule,
                'suggestedSchedule' => $suggestedSchedule,
                'scheduleStatus' => $scheduleStatus,
                'openSession' => $openSession,
            ]);
        }

        $student = $user->student?->load('schoolClass');

        return view('dashboard.student', [
            'student' => $student,
            'todayAttendance' => $student
                ? Attendance::with('session.subject')
                    ->where('student_id', $student->id)
                    ->whereDate('scanned_at', today())
                    ->latest('scanned_at')
                    ->get()
                : collect(),
            'recent' => $student
                ? $student->attendances()
                    ->with(['session.subject', 'session.schoolClass'])
                    ->latest('scanned_at')
                    ->limit(8)
                    ->get()
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

    private function teacherTitle(?Teacher $teacher): string
    {
        $gender = $teacher?->gender;

        if ($gender === 'P') {
            return 'Ibu';
        }

        if ($gender === 'L') {
            return 'Bapak';
        }

        return 'Bapak/Ibu';
    }
}

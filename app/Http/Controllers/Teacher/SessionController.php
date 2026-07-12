<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Services\DynamicQrService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SessionController extends Controller
{
    public function index()
    {
        $teacher = request()->user()->teacher;
        $day = $this->todayName();
        $time = now()->format('H:i:s');

        $todaySchedules = $teacher
            ? Schedule::with(['schoolClass', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('day_of_week', $day)
                ->where('is_active', true)
                ->orderBy('start_time')
                ->get()
            : collect();

        $allSchedules = $teacher
            ? Schedule::with(['schoolClass', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->orderByRaw("CASE day_of_week WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
                ->orderBy('start_time')
                ->get()
            : collect();

        $currentSchedule = $todaySchedules->first(function ($schedule) use ($time) {
            return $schedule->start_time <= $time && $schedule->end_time >= $time;
        });

        $nextSchedule = $todaySchedules->first(function ($schedule) use ($time) {
            return $schedule->start_time > $time;
        });

        $suggestedSchedule = $currentSchedule ?: $nextSchedule;

        $scheduleStatus = $currentSchedule
            ? 'Sedang berlangsung'
            : ($nextSchedule ? 'Jadwal berikutnya' : 'Tidak ada jadwal otomatis hari ini');

        $sessions = AttendanceSession::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(12);

        $openSession = AttendanceSession::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if ($openSession && ! $openSession->isOpen()) {
            $openSession = null;
        }

        return view('teacher.sessions.index', compact(
            'teacher',
            'day',
            'todaySchedules',
            'allSchedules',
            'currentSchedule',
            'nextSchedule',
            'suggestedSchedule',
            'scheduleStatus',
            'sessions',
            'openSession'
        ));
    }

    public function store(Request $request)
    {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'late_after_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'session_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:120'],
            'mode' => ['nullable', 'in:auto,manual'],
        ]);

        $schedule = null;

        if (filled($data['schedule_id'] ?? null)) {
            $schedule = Schedule::with(['schoolClass', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->findOrFail($data['schedule_id']);
        } else {
            $schedule = $this->autoScheduleFor($teacher->id);
        }

        if (! $schedule) {
            return back()->with('error', 'Tidak ada jadwal yang bisa dibuka. Gunakan Mode Testing untuk memilih jadwal manual.');
        }

        $session = DB::transaction(function () use ($teacher, $schedule, $data) {
            AttendanceSession::where('teacher_id', $teacher->id)
                ->where('status', 'open')
                ->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

            return AttendanceSession::create([
                'uuid' => (string) Str::uuid(),
                'schedule_id' => $schedule->id,
                'teacher_id' => $teacher->id,
                'school_class_id' => $schedule->school_class_id,
                'subject_id' => $schedule->subject_id,
                'opened_at' => now(),
                'status' => 'open',
                'late_after_minutes' => (int) ($data['late_after_minutes'] ?? 5),
            'session_duration_minutes' => (int) ($data['session_duration_minutes'] ?? 15),
                'token_version' => 1,
            ]);
        });

        return redirect()->route('teacher.sessions.show', $session)
            ->with('success', 'Sesi absensi berhasil dibuka.');
    }

    public function show(AttendanceSession $session)
    {
        $this->own($session);

        $session->load(['schoolClass', 'subject', 'attendances.student.user']);

        return view('teacher.sessions.show', compact('session'));
    }

    public function token(AttendanceSession $session, DynamicQrService $qrService)
    {
        $this->own($session);

        try {
            $data = $qrService->generate($session);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 410);
        }

        $result = (new Builder(
            writer: new SvgWriter(),
            writerOptions: [],
            validateResult: false,
            data: $data['token'],
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 340,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        ))->build();

        return response()->json($data + [
            'svg' => 'data:image/svg+xml;base64,'.base64_encode($result->getString()),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function attendance(AttendanceSession $session)
    {
        $this->own($session);

        $items = $session->attendances()
            ->with('student.user')
            ->latest('scanned_at')
            ->get()
            ->map(fn ($attendance) => [
                'name' => $attendance->student->user->name,
                'nis' => $attendance->student->nis,
                'status' => $attendance->status,
                'time' => $attendance->scanned_at?->format('H:i:s'),
            ]);

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
        ]);
    }

    public function close(AttendanceSession $session)
    {
        $this->own($session);

        if (! $session->isOpen()) {
            return redirect()->route('teacher.sessions.index')
                ->with('error', 'Sesi absensi sudah ditutup.');
        }

        $session->update([
            'status' => 'closed',
            'closed_at' => now(),
            'token_version' => $session->token_version + 1,
        ]);

        return redirect()->route('teacher.sessions.index')
            ->with('success', 'Sesi absensi ditutup.');
    }

    private function autoScheduleFor(int $teacherId): ?Schedule
    {
        $day = $this->todayName();
        $time = now()->format('H:i:s');

        $todaySchedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $day)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        return $todaySchedules->first(fn ($schedule) => $schedule->start_time <= $time && $schedule->end_time >= $time)
            ?: $todaySchedules->first(fn ($schedule) => $schedule->start_time > $time);
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

    private function own(AttendanceSession $session): void
    {
        abort_unless((int) $session->teacher_id === (int) request()->user()->teacher->id, 403);
    }
}

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


    public function store(\Illuminate\Http\Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            abort(403);
        }

        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'late_after_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'session_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:120'],
        ]);

        $schedule = \App\Models\Schedule::with(['subject', 'schoolClass'])
            ->where('teacher_id', $teacher->id)
            ->where('id', $data['schedule_id'])
            ->where('is_active', true)
            ->first();

        if (! $schedule) {
            return back()
                ->withInput()
                ->withErrors(['schedule_id' => 'Jadwal tidak ditemukan atau bukan jadwal mengajar Anda.']);
        }

        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $todayName = $dayNames[((int) now()->format('N')) - 1] ?? 'Senin';

        if ((string) $schedule->day_of_week !== $todayName) {
            return back()
                ->withInput()
                ->withErrors(['schedule_id' => 'Sesi hanya dapat dibuka pada hari sesuai jadwal.']);
        }

        $startAt = \Illuminate\Support\Carbon::parse(now()->toDateString() . ' ' . substr((string) $schedule->start_time, 0, 8));
        $endAt = \Illuminate\Support\Carbon::parse(now()->toDateString() . ' ' . substr((string) $schedule->end_time, 0, 8));

        if (now()->lt($startAt)) {
            return back()
                ->withInput()
                ->withErrors(['schedule_id' => 'Sesi belum bisa dibuka. Jadwal dimulai pukul ' . $startAt->format('H:i') . ' WIB.']);
        }

        if (now()->gt($endAt)) {
            return back()
                ->withInput()
                ->withErrors(['schedule_id' => 'Sesi tidak bisa dibuka karena jam pelajaran sudah selesai.']);
        }

        $openSession = \App\Models\AttendanceSession::where('teacher_id', $teacher->id)
            ->where('status', 'open')
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();

        if ($openSession && $openSession->isOpen()) {
            return redirect()->route('teacher.sessions.show', $openSession)
                ->with('success', 'Masih ada sesi absensi aktif.');
        }

        $payload = [
            'schedule_id' => $schedule->id,
            'teacher_id' => $teacher->id,
            'school_class_id' => $schedule->school_class_id,
            'subject_id' => $schedule->subject_id,
            'opened_at' => now(),
            'closed_at' => null,
            'status' => 'open',
            'late_after_minutes' => (int) ($data['late_after_minutes'] ?? 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_sessions', 'uuid')) {
            $payload['uuid'] = (string) \Illuminate\Support\Str::uuid();
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_sessions', 'session_duration_minutes')) {
            $payload['session_duration_minutes'] = (int) ($data['session_duration_minutes'] ?? 15);
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_sessions', 'token_version')) {
            $payload['token_version'] = 1;
        }

        $sessionId = \Illuminate\Support\Facades\DB::table('attendance_sessions')->insertGetId($payload);
        $session = \App\Models\AttendanceSession::findOrFail($sessionId);

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

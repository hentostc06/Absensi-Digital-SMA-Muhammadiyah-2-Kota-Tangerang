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

        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->orderByRaw("CASE day_of_week WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('start_time')
            ->get();

        $sessions = AttendanceSession::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(12);

        return view('teacher.sessions.index', compact('schedules', 'sessions'));
    }

    public function store(Request $request)
    {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'late_after_minutes' => ['required', 'integer', 'min:0', 'max:120'],
        ]);

        $schedule = Schedule::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->findOrFail($data['schedule_id']);

        $session = DB::transaction(function () use ($teacher, $schedule, $data) {
            AttendanceSession::where('teacher_id', $teacher->id)
                ->where('status', 'open')
                ->update(['status' => 'closed', 'closed_at' => now()]);

            return AttendanceSession::create([
                'uuid' => (string) Str::uuid(),
                'schedule_id' => $schedule->id,
                'teacher_id' => $teacher->id,
                'school_class_id' => $schedule->school_class_id,
                'subject_id' => $schedule->subject_id,
                'opened_at' => now(),
                'status' => 'open',
                'late_after_minutes' => $data['late_after_minutes'],
                'token_version' => 1,
            ]);
        });

        return redirect()->route('teacher.sessions.show', $session)->with('success', 'Sesi absensi berhasil dibuka.');
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

        return response()->json(['count' => $items->count(), 'items' => $items]);
    }

    public function close(AttendanceSession $session)
    {
        $this->own($session);

        if (! $session->isOpen()) {
            return redirect()->route('teacher.sessions.index')->with('error', 'Sesi absensi sudah ditutup.');
        }

        $session->update([
            'status' => 'closed',
            'closed_at' => now(),
            'token_version' => $session->token_version + 1,
        ]);

        return redirect()->route('teacher.sessions.index')->with('success', 'Sesi absensi ditutup.');
    }

    private function own(AttendanceSession $session): void
    {
        abort_unless((int) $session->teacher_id === (int) request()->user()->teacher->id, 403);
    }
}

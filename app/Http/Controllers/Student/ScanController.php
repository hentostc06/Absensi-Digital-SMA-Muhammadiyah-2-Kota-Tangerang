<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $student = $this->studentForUser($request->user());
        $recentAttendances = collect();

        if ($student && Schema::hasTable('attendances')) {
            $timeColumn = Schema::hasColumn('attendances', 'scanned_at') ? 'scanned_at' : 'created_at';

            $recentAttendances = DB::table('attendances')
                ->where('student_id', $student->id)
                ->orderByDesc($timeColumn)
                ->limit(10)
                ->get()
                ->map(function ($attendance) {
                    $sessionId = $attendance->attendance_session_id
                        ?? $attendance->session_id
                        ?? null;

                    $session = $sessionId && Schema::hasTable('attendance_sessions')
                        ? DB::table('attendance_sessions')->where('id', $sessionId)->first()
                        : null;

                    if (! $session) {
                        $session = (object) [
                            'id' => $sessionId,
                            'subject_id' => null,
                        ];
                    }

                    $subjectName = $this->subjectName($session);

                    $session->subject = (object) [
                        'name' => $subjectName,
                    ];

                    $attendance->session = $session;
                    $attendance->subject = $session->subject;
                    $attendance->subject_name = $subjectName;

                    return $attendance;
                });
        }

        return view('student.scan', [
            'student' => $student,
            'recent' => $recentAttendances,
            'recentAttendances' => $recentAttendances,
            'attendances' => $recentAttendances,
            'history' => $recentAttendances,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $student = $this->studentForUser($request->user());

        if (! $student) {
            return response()->json([
                'ok' => false,
                'message' => 'Data siswa tidak ditemukan pada akun ini.',
            ], 422);
        }

        $token = $this->normalizeToken((string) $request->input('token'));
        $payload = $this->payloadFromToken($token);
        $sessionId = $payload['sid']
            ?? $payload['session_id']
            ?? $payload['attendance_session_id']
            ?? null;

        if (! $sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'QR Code tidak valid. Silakan scan QR terbaru dari guru.',
            ], 422);
        }

        if (! $this->tokenNotExpired($payload)) {
            return response()->json([
                'ok' => false,
                'message' => 'QR Code sudah kedaluwarsa. Silakan scan QR terbaru.',
            ], 422);
        }

        $session = DB::table('attendance_sessions')->where('id', (int) $sessionId)->first();

        if (! $session) {
            return response()->json([
                'ok' => false,
                'message' => 'Sesi absensi tidak ditemukan.',
            ], 404);
        }

        if (! $this->sessionIsOpen($session)) {
            return response()->json([
                'ok' => false,
                'message' => 'Sesi absensi sudah berakhir.',
            ], 422);
        }

        if (! $this->studentAllowedForSession($student, $session)) {
            return response()->json([
                'ok' => false,
                'message' => 'QR Code ini bukan untuk kelas Anda.',
            ], 403);
        }

        $subjectName = $this->subjectName($session);
        $now = now();
        $timeLabel = $now->format('H:i');
        $status = $this->attendanceStatus($session, $now);

        $sessionColumn = Schema::hasColumn('attendances', 'attendance_session_id')
            ? 'attendance_session_id'
            : 'session_id';

        $existing = DB::table('attendances')
            ->where('student_id', $student->id)
            ->where($sessionColumn, $session->id)
            ->first();

        if ($existing) {
            $existingTime = $this->attendanceTimeLabel($existing);

            return response()->json([
                'ok' => true,
                'duplicate' => true,
                'subject' => $subjectName,
                'time' => $existingTime,
                'status' => $existing->status ?? 'hadir',
                'message' => "Anda sudah absen pada pelajaran {$subjectName} pada {$existingTime}.",
            ]);
        }

        $insert = [
            'student_id' => $student->id,
            $sessionColumn => $session->id,
        ];

        if (Schema::hasColumn('attendances', 'status')) {
            $insert['status'] = $status;
        }

        if (Schema::hasColumn('attendances', 'scanned_at')) {
            $insert['scanned_at'] = $now;
        }

        if (Schema::hasColumn('attendances', 'created_at')) {
            $insert['created_at'] = $now;
        }

        if (Schema::hasColumn('attendances', 'updated_at')) {
            $insert['updated_at'] = $now;
        }

        DB::table('attendances')->insert($insert);

        return response()->json([
            'ok' => true,
            'duplicate' => false,
            'subject' => $subjectName,
            'time' => $timeLabel,
            'status' => $status,
            'message' => "Berhasil absen pada pelajaran {$subjectName} pada {$timeLabel}.",
        ]);
    }

    private function studentForUser($user): ?object
    {
        if (! $user || ! Schema::hasTable('students')) {
            return null;
        }

        try {
            if (method_exists($user, 'student')) {
                $student = $user->student()->first();

                if ($student) {
                    return $student;
                }
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        if (Schema::hasColumn('students', 'user_id')) {
            $student = DB::table('students')->where('user_id', $user->id)->first();

            if ($student) {
                return $student;
            }
        }

        $username = (string) ($user->username ?? '');

        foreach (['nis', 'username', 'student_number'] as $column) {
            if ($username !== '' && Schema::hasColumn('students', $column)) {
                $student = DB::table('students')->where($column, $username)->first();

                if ($student) {
                    return $student;
                }
            }
        }

        return null;
    }

    private function normalizeToken(string $token): string
    {
        $token = trim($token);

        if (str_contains($token, 'token=')) {
            $query = parse_url($token, PHP_URL_QUERY);
            parse_str((string) $query, $params);

            if (! empty($params['token'])) {
                return trim((string) $params['token']);
            }
        }

        return $token;
    }

    private function payloadFromToken(string $token): array
    {
        $firstPart = explode('.', $token)[0] ?? '';
        $payload = $this->base64UrlDecode($firstPart);

        if (! $payload) {
            return [];
        }

        $json = json_decode($payload, true);

        return is_array($json) ? $json : [];
    }

    private function base64UrlDecode(string $value): ?string
    {
        $value = strtr($value, '-_', '+/');
        $padding = strlen($value) % 4;

        if ($padding) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($value, true);

        return $decoded === false ? null : $decoded;
    }

    private function tokenNotExpired(array $payload): bool
    {
        $expires = $payload['exp']
            ?? $payload['expires_at']
            ?? $payload['expired_at']
            ?? null;

        if (! $expires) {
            return true;
        }

        try {
            if (is_numeric($expires)) {
                return now()->timestamp <= (int) $expires;
            }

            return now()->lte(Carbon::parse($expires));
        } catch (\Throwable $exception) {
            return true;
        }
    }


    private function sessionIsOpen(object $session): bool
    {
        if (isset($session->status)) {
            $status = strtolower((string) $session->status);

            if (in_array($status, ['closed', 'close', 'selesai', 'ditutup', 'inactive'], true)) {
                return false;
            }
        }

        if (isset($session->closed_at) && filled($session->closed_at)) {
            return false;
        }

        $durationMinutes = (int) ($session->session_duration_minutes ?? 15);

        if ($durationMinutes <= 0) {
            $durationMinutes = 15;
        }

        $openedRaw = $session->opened_at
            ?? $session->started_at
            ?? $session->start_time
            ?? $session->created_at
            ?? null;

        if ($openedRaw) {
            try {
                $expiredAt = \Illuminate\Support\Carbon::parse($openedRaw)->addMinutes($durationMinutes);

                if (now()->greaterThan($expiredAt)) {
                    $updates = [];

                    if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_sessions', 'closed_at')) {
                        $updates['closed_at'] = now();
                    }

                    if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_sessions', 'status')) {
                        $updates['status'] = 'closed';
                    }

                    if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_sessions', 'token_version')) {
                        $currentVersion = (int) ($session->token_version ?? 0);
                        $updates['token_version'] = $currentVersion + 1;
                    }

                    if ($updates && isset($session->id)) {
                        \Illuminate\Support\Facades\DB::table('attendance_sessions')
                            ->where('id', $session->id)
                            ->update($updates);
                    }

                    return false;
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return true;
    }

    private function studentAllowedForSession(object $student, object $session): bool
    {
        $studentClassId = $student->school_class_id
            ?? $student->class_id
            ?? null;

        $sessionClassId = $session->school_class_id
            ?? $session->class_id
            ?? null;

        if (! $studentClassId || ! $sessionClassId) {
            return true;
        }

        return (int) $studentClassId === (int) $sessionClassId;
    }

    private function subjectName(object $session): string
    {
        foreach (['subject_name', 'lesson_name', 'mapel'] as $column) {
            if (isset($session->{$column}) && filled($session->{$column})) {
                return (string) $session->{$column};
            }
        }

        if (isset($session->subject_id) && Schema::hasTable('subjects')) {
            $name = DB::table('subjects')->where('id', $session->subject_id)->value('name');

            if ($name) {
                return (string) $name;
            }
        }

        if (isset($session->schedule_id)) {
            foreach (['schedules', 'class_schedules'] as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $schedule = DB::table($table)->where('id', $session->schedule_id)->first();

                if (! $schedule) {
                    continue;
                }

                if (isset($schedule->subject_id) && Schema::hasTable('subjects')) {
                    $name = DB::table('subjects')->where('id', $schedule->subject_id)->value('name');

                    if ($name) {
                        return (string) $name;
                    }
                }

                foreach (['subject_name', 'lesson_name', 'mapel'] as $column) {
                    if (isset($schedule->{$column}) && filled($schedule->{$column})) {
                        return (string) $schedule->{$column};
                    }
                }
            }
        }

        return 'Mata Pelajaran';
    }

    private function attendanceStatus(object $session, Carbon $now): string
    {
        $lateAfter = (int) ($session->late_after_minutes ?? 0);

        if ($lateAfter <= 0) {
            return 'hadir';
        }

        $startRaw = $session->started_at
            ?? $session->start_time
            ?? $session->opened_at
            ?? $session->created_at
            ?? null;

        if (! $startRaw) {
            return 'hadir';
        }

        try {
            $start = Carbon::parse($startRaw);

            return $now->gt($start->copy()->addMinutes($lateAfter))
                ? 'terlambat'
                : 'hadir';
        } catch (\Throwable $exception) {
            return 'hadir';
        }
    }

    private function attendanceTimeLabel(object $attendance): string
    {
        $raw = $attendance->scanned_at
            ?? $attendance->created_at
            ?? now();

        try {
            return Carbon::parse($raw)->format('H:i');
        } catch (\Throwable $exception) {
            return now()->format('H:i');
        }
    }
}

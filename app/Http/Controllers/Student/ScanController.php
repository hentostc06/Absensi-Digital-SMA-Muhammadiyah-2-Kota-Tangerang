<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Services\DynamicQrService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScanController extends Controller
{
    public function index()
    {
        $student = request()->user()->student;

        return view('student.scan', [
            'recent' => $student
                ? $student->attendances()->with('session.subject')->latest('scanned_at')->limit(5)->get()
                : collect(),
        ]);
    }

    public function store(Request $request, DynamicQrService $qrService)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:2000'],
        ]);

        $student = $request->user()->student;

        if (! $student) {
            return $this->fail('Akun ini belum terhubung ke data siswa.', 422);
        }

        $payload = $this->readPayload($data['token']);

        if (! $payload) {
            return $this->fail('QR Code tidak valid. Pastikan yang discan adalah QR absensi dari guru.', 422);
        }

        $session = AttendanceSession::with(['schoolClass', 'subject'])->find((int) ($payload['sid'] ?? 0));

        if (! $session) {
            return $this->fail('Sesi absensi tidak ditemukan.', 404);
        }

        if (! $session->isOpen()) {
            return $this->fail('Sesi absensi sudah ditutup.', 422);
        }

        if ((int) $student->school_class_id !== (int) $session->school_class_id) {
            return $this->fail(
                'QR ini untuk kelas '.$session->schoolClass->name.', sedangkan akun Anda bukan kelas tersebut.',
                403
            );
        }

        if (! $qrService->validate($data['token'], $session)) {
            return $this->fail('QR Code sudah kedaluwarsa. Scan ulang QR terbaru yang tampil di layar guru.', 422);
        }

        try {
            $result = DB::transaction(function () use ($session, $student, $request) {
                $existing = Attendance::where('attendance_session_id', $session->id)
                    ->where('student_id', $student->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return [
                        'duplicate' => true,
                        'attendance' => $existing,
                    ];
                }

                $status = now()->greaterThan($session->opened_at->copy()->addMinutes($session->late_after_minutes))
                    ? 'terlambat'
                    : 'hadir';

                $attendance = Attendance::create([
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => $status,
                    'scanned_at' => now(),
                    'source' => 'qr',
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 500),
                ]);

                return [
                    'duplicate' => false,
                    'attendance' => $attendance,
                ];
            });
        } catch (QueryException) {
            return $this->fail('Anda sudah melakukan absensi pada sesi ini.', 409);
        }

        if ($result['duplicate']) {
            return response()->json([
                'ok' => true,
                'duplicate' => true,
                'message' => 'Anda sudah melakukan absensi pada sesi ini.',
                'status' => $result['attendance']->status,
                'subject' => $session->subject->name,
                'time' => optional($result['attendance']->scanned_at)->format('H:i:s'),
            ]);
        }

        return response()->json([
            'ok' => true,
            'duplicate' => false,
            'message' => 'Absensi berhasil dicatat.',
            'status' => $result['attendance']->status,
            'subject' => $session->subject->name,
            'time' => $result['attendance']->scanned_at->format('H:i:s'),
        ]);
    }

    private function readPayload(string $token): ?array
    {
        $parts = explode('.', trim($token));

        if (count($parts) !== 2) {
            return null;
        }

        $encoded = $parts[0];
        $pad = str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $json = base64_decode(strtr($encoded.$pad, '-_', '+/'), true);

        if (! $json) {
            return null;
        }

        $payload = json_decode($json, true);

        return is_array($payload) ? $payload : null;
    }

    private function fail(string $message, int $status = 422)
    {
        return response()->json([
            'ok' => false,
            'message' => $message,
        ], $status);
    }
}

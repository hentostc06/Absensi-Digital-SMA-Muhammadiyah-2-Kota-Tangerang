<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualAttendanceController extends Controller
{
    public function store(Request $request, AttendanceSession $session)
    {
        abort_unless((int) $session->teacher_id === (int) $request->user()->teacher->id, 403);

        if (! $session->isOpen()) {
            return back()->with('error', 'Sesi absensi sudah ditutup. Absensi manual tidak dapat diubah.');
        }

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,alpa'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $student = Student::where('school_class_id', $session->school_class_id)->findOrFail($data['student_id']);

        DB::transaction(function () use ($session, $student, $data, $request) {
            Attendance::updateOrCreate(
                ['attendance_session_id' => $session->id, 'student_id' => $student->id],
                [
                    'status' => $data['status'],
                    'scanned_at' => now(),
                    'source' => 'manual',
                    'notes' => $data['notes'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 500),
                ]
            );
        });

        return back()->with('success', 'Absensi manual tersimpan.');
    }
}

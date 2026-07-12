<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function rows(array $filters): Collection
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $classId = $filters['class_id'] ?? null;
        $subjectId = $filters['subject_id'] ?? null;
        $statusFilter = $filters['status'] ?? null;

        $sessions = AttendanceSession::with([
                'schoolClass',
                'subject',
                'teacher.user',
                'attendances.student.user',
                'attendances.student.schoolClass',
            ])
            ->when($from, fn ($query) => $query->whereDate('opened_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('opened_at', '<=', $to))
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->orderByDesc('opened_at')
            ->get();

        if ($sessions->isEmpty()) {
            return collect();
        }

        $studentsByClass = Student::with(['user', 'schoolClass'])
            ->whereIn('school_class_id', $sessions->pluck('school_class_id')->filter()->unique()->values())
            ->orderBy('nis')
            ->get()
            ->groupBy('school_class_id');

        $rows = collect();

        foreach ($sessions as $session) {
            $students = $studentsByClass->get($session->school_class_id, collect());
            $attendancesByStudent = $session->attendances->keyBy('student_id');

            foreach ($students as $student) {
                $attendance = $attendancesByStudent->get($student->id);
                $status = $this->resolveStatus($session, $attendance);

                if ($statusFilter && $status !== strtolower((string) $statusFilter)) {
                    continue;
                }

                $scanTime = $this->scanTime($attendance);
                $sessionTime = $this->sessionTime($session);

                $rows->push((object) [
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                    'tanggal' => $sessionTime ? $sessionTime->format('d-m-Y') : '-',
                    'jam_sesi' => $sessionTime ? $sessionTime->format('H:i') : '-',
                    'waktu_scan' => $scanTime ? $scanTime->format('H:i:s') : '-',
                    'tanggal_waktu' => $scanTime
                        ? $scanTime->format('d-m-Y H:i')
                        : (($sessionTime ? $sessionTime->format('d-m-Y H:i') : '-') . ' / Belum scan'),
                    'nis' => $student->nis ?? '-',
                    'nama' => $student->user->name ?? '-',
                    'kelas' => $student->schoolClass->name ?? $session->schoolClass->name ?? '-',
                    'mapel' => $session->subject->name ?? '-',
                    'guru' => $session->teacher->user->name ?? '-',
                    'status' => $status,
                    'status_label' => ucfirst($status),
                    'is_absent' => $status === 'alpa',
                    'sort_time' => $scanTime ?: $sessionTime,
                ]);
            }
        }

        return $rows
            ->sortByDesc(fn ($row) => optional($row->sort_time)->timestamp ?? 0)
            ->values();
    }

    public function paginateRows(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $rows = $this->rows($filters);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    private function resolveStatus($session, $attendance): string
    {
        if (! $attendance) {
            return 'alpa';
        }

        $scanTime = $this->scanTime($attendance);
        $sessionTime = $this->sessionTime($session);

        if (! $scanTime || ! $sessionTime) {
            return strtolower((string) ($attendance->status ?: 'hadir'));
        }

        $lateAfterMinutes = (int) ($session->late_after_minutes ?? 5);

        if ($lateAfterMinutes <= 0) {
            $lateAfterMinutes = 5;
        }

        $lateLimit = $sessionTime->copy()->addMinutes($lateAfterMinutes);

        if ($scanTime->greaterThan($lateLimit)) {
            return 'terlambat';
        }

        return 'hadir';
    }

    private function scanTime($attendance): ?Carbon
    {
        if (! $attendance) {
            return null;
        }

        $raw = $attendance->scanned_at
            ?? $attendance->created_at
            ?? $attendance->updated_at
            ?? null;

        return $raw ? Carbon::parse($raw) : null;
    }

    private function sessionTime($session): ?Carbon
    {
        $raw = $session->opened_at
            ?? $session->started_at
            ?? $session->start_time
            ?? $session->created_at
            ?? null;

        return $raw ? Carbon::parse($raw) : null;
    }
}

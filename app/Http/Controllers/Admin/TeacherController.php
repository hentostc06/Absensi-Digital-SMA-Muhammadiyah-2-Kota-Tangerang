<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $items = Teacher::with('user')
            ->when($request->q, function ($query, $keyword) {
                $query->where('niy_nbm', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('username', 'like', "%{$keyword}%");
                    });
            })
            ->orderBy('niy_nbm')
            ->paginate(15)
            ->withQueryString();

        return view('admin.teachers.index', compact('items'));
    }

    public function show(Teacher $teacher)
    {
        $teacher->load('user');

        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->orderByRaw("CASE day_of_week WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('start_time')
            ->get();

        $sessionsCount = AttendanceSession::where('teacher_id', $teacher->id)->count();

        return view('admin.teachers.show', [
            'teacher' => $teacher,
            'schedules' => $schedules,
            'sessionsCount' => $sessionsCount,
            'subjectCount' => $schedules->pluck('subject_id')->unique()->count(),
            'classCount' => $schedules->pluck('school_class_id')->unique()->count(),
        ]);
    }

    public function create()
    {
        return view('admin.teachers.form', [
            'teacher' => new Teacher,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => 'guru',
                'is_active' => true,
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'niy_nbm' => $data['niy_nbm'],
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('user');

        return view('admin.teachers.form', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $this->data($request, $teacher);

        DB::transaction(function () use ($data, $teacher) {
            $teacher->user->update([
                'name' => $data['name'],
                'username' => $data['username'],
                'is_active' => $data['is_active'] ?? true,
            ] + (! empty($data['password']) ? ['password' => $data['password']] : []));

            $teacher->update([
                'niy_nbm' => $data['niy_nbm'],
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->load('user');

        if ((int) $teacher->user_id === (int) auth()->id()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        $hasSchedule = Schedule::where('teacher_id', $teacher->id)->exists();
        $hasSession = AttendanceSession::where('teacher_id', $teacher->id)->exists();

        if ($hasSchedule || $hasSession) {
            $teacher->user?->update(['is_active' => false]);

            return back()->with(
                'success',
                'Guru tidak dihapus permanen karena sudah memiliki jadwal atau riwayat absensi. Akun guru sudah dinonaktifkan agar data laporan tetap aman.'
            );
        }

        DB::transaction(function () use ($teacher) {
            $user = $teacher->user;

            $teacher->delete();
            $user?->delete();
        });

        return back()->with('success', 'Data guru berhasil dihapus.');
    }

    private function data(Request $request, ?Teacher $teacher = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'niy_nbm' => ['required', 'string', 'max:30', Rule::unique('teachers', 'niy_nbm')->ignore($teacher?->id)],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($teacher?->user_id)],
            'gender' => ['nullable', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [$teacher ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}

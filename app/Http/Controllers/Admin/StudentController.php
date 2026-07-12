<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{

    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $selectedClass = $request->input('class_id');

        $classes = SchoolClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        $items = Student::with(['user', 'schoolClass'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery->where('nis', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when(filled($selectedClass), function ($query) use ($selectedClass) {
                $query->where('school_class_id', $selectedClass);
            })
            ->orderBy('nis')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', compact(
            'items',
            'classes',
            'selectedClass',
            'search'
        ));
    }

    public function create()
    {
        return view('admin.students.form', [
            'student' => new Student,
            'classes' => SchoolClass::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['nis'],
                'password' => $data['password'],
                'role' => 'siswa',
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'school_class_id' => $data['school_class_id'],
                'nis' => $data['nis'],
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        $student->load('user');

        return view('admin.students.form', [
            'student' => $student,
            'classes' => SchoolClass::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $data = $this->data($request, $student);

        DB::transaction(function () use ($data, $student) {
            $student->user->update([
                'name' => $data['name'],
                'username' => $data['nis'],
                'is_active' => $data['is_active'] ?? true,
            ] + (! empty($data['password']) ? ['password' => $data['password']] : []));

            $student->update([
                'school_class_id' => $data['school_class_id'],
                'nis' => $data['nis'],
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->load('user');

        if ((int) $student->user_id === (int) auth()->id()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        if ($student->attendances()->exists()) {
            $student->user?->update(['is_active' => false]);

            return back()->with(
                'success',
                'Siswa tidak dihapus permanen karena sudah memiliki riwayat absensi. Akun siswa sudah dinonaktifkan agar data laporan tetap aman.'
            );
        }

        DB::transaction(function () use ($student) {
            $user = $student->user;

            $student->delete();
            $user?->delete();
        });

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    private function data(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nis' => ['required', 'string', 'max:20', Rule::unique('students', 'nis')->ignore($student?->id)],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'gender' => ['nullable', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => [$student ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}

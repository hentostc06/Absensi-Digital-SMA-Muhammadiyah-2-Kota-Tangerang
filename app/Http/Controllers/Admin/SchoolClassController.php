<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $items = SchoolClass::query()
            ->when($request->q, function ($query, $keyword) {
                $query->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('grade', 'like', "%{$keyword}%")
                    ->orWhere('academic_year', 'like', "%{$keyword}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.classes.index', compact('items'));
    }

    public function show(SchoolClass $schoolClass)
    {
        $schoolClass->load(['students.user']);

        $schedules = Schedule::with(['teacher.user', 'subject'])
            ->where('school_class_id', $schoolClass->id)
            ->where('is_active', true)
            ->orderByRaw("CASE day_of_week WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('start_time')
            ->get();

        return view('admin.classes.show', [
            'class' => $schoolClass,
            'item' => $schoolClass,
            'students' => $schoolClass->students->sortBy(fn ($student) => $student->user->name ?? '')->values(),
            'schedules' => $schedules,
        ]);
    }

    public function create()
    {
        $item = new SchoolClass();

        return view('admin.classes.form', [
            'item' => $item,
            'class' => $item,
        ]);
    }

    public function store(Request $request)
    {
        SchoolClass::create($this->data($request));

        return redirect()->route('admin.classes.index')
            ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class)
    {
        return view('admin.classes.form', [
            'item' => $class,
            'class' => $class,
        ]);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $class->update($this->data($request, $class));

        return redirect()->route('admin.classes.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->loadCount(['students']);

        $hasStudents = $class->students_count > 0;
        $hasSchedules = Schedule::where('school_class_id', $class->id)->exists();

        if ($hasStudents || $hasSchedules) {
            if (Schema::hasColumn('school_classes', 'is_active')) {
                $class->update(['is_active' => false]);

                return back()->with(
                    'success',
                    'Kelas tidak dihapus permanen karena sudah memiliki siswa atau jadwal. Status kelas sudah dinonaktifkan agar data tetap aman.'
                );
            }

            return back()->with(
                'error',
                'Kelas tidak dapat dihapus karena sudah memiliki siswa atau jadwal. Hapus/pindahkan relasi terlebih dahulu.'
            );
        }

        $class->delete();

        return back()->with('success', 'Data kelas berhasil dihapus.');
    }

    private function data(Request $request, ?SchoolClass $class = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('school_classes', 'code')->ignore($class?->id)],
            'name' => ['required', 'string', 'max:100'],
            'grade' => ['required', 'string', 'max:30'],
            'academic_year' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (Schema::hasColumn('school_classes', 'is_active')) {
            $data['is_active'] = $request->boolean('is_active', true);
        } else {
            unset($data['is_active']);
        }

        return $data;
    }
}

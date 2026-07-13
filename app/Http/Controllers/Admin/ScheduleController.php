<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $teachers = \App\Models\Teacher::with('user')
            ->get()
            ->sortBy(fn ($teacher) => $teacher->user->name ?? '')
            ->values();

        $subjects = \App\Models\Subject::query()
            ->orderBy('name')
            ->get();

        $query = \App\Models\Schedule::with(['teacher.user', 'schoolClass', 'subject'])
            ->when($request->filled('teacher_id'), function ($query) use ($request) {
                $query->where('teacher_id', $request->integer('teacher_id'));
            })
            ->when($request->filled('day'), function ($query) use ($request) {
                $query->where('day_of_week', $request->input('day'));
            })
            ->when($request->filled('subject_id'), function ($query) use ($request) {
                $query->where('subject_id', $request->integer('subject_id'));
            })
            ->orderByRaw("CASE day_of_week WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('start_time');

        return view('admin.schedules.index', [
            'items' => $query->paginate(20)->withQueryString(),
            'teachers' => $teachers,
            'subjects' => $subjects,
            'days' => $days,
            'selectedTeacherId' => $request->input('teacher_id'),
            'selectedSubjectId' => $request->input('subject_id'),
            'selectedDay' => $request->input('day'),
        ]);
    }



    public function create()
    {
        return $this->form(new Schedule());
    }

    public function store(Request $request)
    {
        $data = $this->data($request);
        $this->ensureNoConflict($data);
        Schedule::create($data);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal ditambahkan.');
    }

    public function edit(Schedule $schedule)
    {
        return $this->form($schedule);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $data = $this->data($request);
        $this->ensureNoConflict($data, $schedule);
        $schedule->update($data);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal dihapus.');
    }

    private function form(Schedule $item)
    {
        return view('admin.schedules.form', compact('item') + [
            'teachers' => Teacher::with('user')->get(),
            'classes' => SchoolClass::where('is_active', true)->get(),
            'subjects' => Subject::where('is_active', true)->get(),
        ]);
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'day_of_week' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function ensureNoConflict(array $data, ?Schedule $ignore = null): void
    {
        if (! $data['is_active']) {
            return;
        }

        $conflict = Schedule::query()
            ->where('is_active', true)
            ->where('day_of_week', $data['day_of_week'])
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->where(function ($query) use ($data) {
                $query->where('teacher_id', $data['teacher_id'])
                    ->orWhere('school_class_id', $data['school_class_id']);
            })
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => 'Jadwal bentrok dengan guru atau kelas pada jam yang sama.',
            ]);
        }
    }
}

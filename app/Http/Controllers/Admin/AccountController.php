<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role', 'siswa');

        if (! in_array($role, ['admin', 'guru', 'siswa'], true)) {
            $role = 'siswa';
        }

        $accounts = User::with(['teacher', 'student.schoolClass'])
            ->where('role', $role)
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim($request->q);

                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.accounts.index', [
            'accounts' => $accounts,
            'classes' => SchoolClass::where('is_active', true)->orderBy('grade')->orderBy('name')->get(),
            'role' => $role,
            'totalAdmin' => User::where('role', 'admin')->count(),
            'totalGuru' => User::where('role', 'guru')->count(),
            'totalSiswa' => User::where('role', 'siswa')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'name' => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:120', 'unique:users,email'],
            'password' => ['nullable', Password::min(6)],
            'is_active' => ['nullable', 'boolean'],
            'niy_nbm' => ['nullable', 'string', 'max:30', 'unique:teachers,niy_nbm'],
            'school_class_id' => ['nullable', 'required_if:role,siswa', 'exists:school_classes,id'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data) {
            $role = $data['role'];
            $password = filled($data['password'] ?? null) ? $data['password'] : $this->defaultPassword($role);

            $generatedNis = $role === 'siswa'
                ? $this->nextStudentNumber()
                : null;

            /*
             * Aturan login:
             * - Admin/Guru login memakai username.
             * - Siswa login memakai NIS.
             * Jika admin tidak mengisi username untuk siswa, sistem otomatis
             * memakai NIS sebagai username login.
             */
            $username = match ($role) {
                'siswa' => filled($data['username'] ?? null) ? $data['username'] : $generatedNis,
                default => filled($data['username'] ?? null) ? $data['username'] : $this->uniqueUsername($role, $data['name']),
            };

            $user = User::create([
                'name' => $data['name'],
                'username' => $username,
                'email' => $data['email'] ?? null,
                'password' => Hash::make($password),
                'role' => $role,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            if ($role === 'guru') {
                Teacher::create([
                    'user_id' => $user->id,
                    'niy_nbm' => filled($data['niy_nbm'] ?? null) ? $data['niy_nbm'] : $this->nextTeacherNumber(),
                    'phone' => $data['phone'] ?? null,
                ]);
            }

            if ($role === 'siswa') {
                Student::create([
                    'user_id' => $user->id,
                    'school_class_id' => $data['school_class_id'] ?? null,
                    'nis' => $generatedNis,
                    'gender' => $data['gender'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);
            }

            session()->flash('generated_password', [
                'name' => $user->name,
                'username' => $user->username,
                'password' => $password,
            ]);
        });

        return back()->with('success', 'Akun berhasil dibuat oleh admin.');
    }

    public function edit(User $account)
    {
        $account->load(['teacher', 'student.schoolClass']);

        return view('admin.accounts.edit', [
            'account' => $account,
            'classes' => SchoolClass::where('is_active', true)->orderBy('grade')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $account)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($account->id)],
            'email' => ['nullable', 'email', 'max:120', Rule::unique('users', 'email')->ignore($account->id)],
            'password' => ['nullable', Password::min(6)],
            'is_active' => ['nullable', 'boolean'],
            'niy_nbm' => ['nullable', 'string', 'max:30'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($account, $data) {
            $account->fill([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            if (filled($data['password'] ?? null)) {
                $account->password = Hash::make($data['password']);
            }

            $account->save();

            if ($account->role === 'guru') {
                $account->teacher()->updateOrCreate(
                    ['user_id' => $account->id],
                    [
                        'niy_nbm' => filled($data['niy_nbm'] ?? null) ? $data['niy_nbm'] : ($account->teacher?->niy_nbm ?? $this->nextTeacherNumber()),
                        'phone' => $data['phone'] ?? null,
                    ]
                );
            }

            if ($account->role === 'siswa') {
                $account->student()->updateOrCreate(
                    ['user_id' => $account->id],
                    [
                        'school_class_id' => $data['school_class_id'] ?: $account->student?->school_class_id,
                        'nis' => $account->student?->nis ?? $this->nextStudentNumber(),
                        'gender' => $data['gender'] ?? null,
                        'phone' => $data['phone'] ?? null,
                        'address' => $data['address'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('admin.accounts.index', ['role' => $account->role])
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function toggle(User $account)
    {
        if ($account->id === auth()->id()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        $account->update(['is_active' => ! $account->is_active]);

        return back()->with('success', 'Status akun berhasil diubah.');
    }

    public function resetPassword(User $account)
    {
        $password = $this->defaultPassword($account->role);
        $account->update(['password' => Hash::make($password)]);

        session()->flash('generated_password', [
            'name' => $account->name,
            'username' => $account->username,
            'password' => $password,
        ]);

        return back()->with('success', 'Password berhasil direset.');
    }


    public function destroy(User $account)
    {
        if ($account->id === auth()->id()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        $role = $account->role;
        $account->load(['teacher', 'student']);

        if ($role === 'guru' && $account->teacher) {
            $teacher = $account->teacher;

            if ($teacher->schedules()->exists() || $teacher->sessions()->exists()) {
                $account->update(['is_active' => false]);

                return redirect()->route('admin.accounts.index', ['role' => $role])
                    ->with('success', 'Akun guru tidak dihapus permanen karena sudah memiliki jadwal atau riwayat absensi. Akun sudah dinonaktifkan.');
            }

            DB::transaction(function () use ($account, $teacher) {
                $teacher->delete();
                $account->delete();
            });

            return redirect()->route('admin.accounts.index', ['role' => $role])
                ->with('success', 'Akun guru berhasil dihapus.');
        }

        if ($role === 'siswa' && $account->student) {
            $student = $account->student;

            if ($student->attendances()->exists()) {
                $account->update(['is_active' => false]);

                return redirect()->route('admin.accounts.index', ['role' => $role])
                    ->with('success', 'Akun siswa tidak dihapus permanen karena sudah memiliki riwayat absensi. Akun sudah dinonaktifkan.');
            }

            DB::transaction(function () use ($account, $student) {
                $student->delete();
                $account->delete();
            });

            return redirect()->route('admin.accounts.index', ['role' => $role])
                ->with('success', 'Akun siswa berhasil dihapus.');
        }

        $account->delete();

        return redirect()->route('admin.accounts.index', ['role' => $role])
            ->with('success', 'Akun berhasil dihapus.');
    }


    private function defaultPassword(string $role): string
    {
        return match ($role) {
            'admin' => 'Admin123!',
            'guru' => 'Guru123!',
            default => 'Siswa123!',
        };
    }

    private function uniqueUsername(string $role, string $name): string
    {
        $base = str($name)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '.')->trim('.')->limit(25, '')->value();
        $base = $base ?: $role;
        $username = "{$role}.{$base}";
        $i = 1;

        while (User::where('username', $username)->exists()) {
            $i++;
            $username = "{$role}.{$base}.{$i}";
        }

        return $username;
    }

    private function nextStudentNumber(): string
    {
        do {
            $nis = now()->format('Y') . str_pad((string) (Student::count() + random_int(1, 999)), 6, '0', STR_PAD_LEFT);
        } while (Student::where('nis', $nis)->exists());

        return $nis;
    }

    private function nextTeacherNumber(): string
    {
        do {
            $number = 'G' . now()->format('Y') . str_pad((string) (Teacher::count() + random_int(1, 999)), 4, '0', STR_PAD_LEFT);
        } while (Teacher::where('niy_nbm', $number)->exists());

        return $number;
    }
}

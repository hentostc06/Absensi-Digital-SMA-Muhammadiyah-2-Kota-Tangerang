<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class AccountSettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('account.settings', [
            'user' => $user,
            'profileRows' => $this->profileRows($user),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                ->withInput();
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => \Illuminate\Support\Str::random(60),
        ])->save();

        return back()->with('success', 'Password akun berhasil diperbarui.');
    }

    private function profileRows($user): array
    {
        $rows = [
            ['label' => 'Nama', 'value' => $user->name ?? '-'],
            ['label' => 'Username / NIS', 'value' => $user->username ?? '-'],
            ['label' => 'Email', 'value' => $user->email ?? '-'],
            ['label' => 'Role', 'value' => strtoupper((string) ($user->role ?? '-'))],
            ['label' => 'Status Akun', 'value' => isset($user->is_active) ? ($user->is_active ? 'Aktif' : 'Nonaktif') : 'Aktif'],
        ];

        $username = (string) ($user->username ?? '');

        if (($user->role ?? null) === 'siswa' && Schema::hasTable('students')) {
            $student = $this->findProfileRecord('students', [
                'user_id' => $user->getKey(),
                'nis' => $username,
                'username' => $username,
                'student_number' => $username,
            ]);

            if ($student) {
                $rows = array_merge($rows, $this->recordRows('students', $student, [
                    'nis' => 'NIS',
                    'name' => 'Nama Siswa',
                    'gender' => 'Jenis Kelamin',
                    'phone' => 'No. HP',
                    'address' => 'Alamat',
                    'class_id' => 'ID Kelas',
                    'school_class_id' => 'ID Kelas',
                ]));
            }
        }

        if (($user->role ?? null) === 'guru' && Schema::hasTable('teachers')) {
            $teacher = $this->findProfileRecord('teachers', [
                'user_id' => $user->getKey(),
                'username' => $username,
                'niy' => $username,
                'nbm' => $username,
                'nip' => $username,
                'teacher_number' => $username,
            ]);

            if ($teacher) {
                $rows = array_merge($rows, $this->recordRows('teachers', $teacher, [
                    'niy' => 'NIY',
                    'nbm' => 'NBM',
                    'nip' => 'NIP',
                    'name' => 'Nama Guru',
                    'gender' => 'Jenis Kelamin',
                    'phone' => 'No. HP',
                    'address' => 'Alamat',
                ]));
            }
        }

        return collect($rows)
            ->filter(fn ($row) => filled($row['value']))
            ->unique('label')
            ->values()
            ->all();
    }

    private function findProfileRecord(string $table, array $conditions): ?object
    {
        foreach ($conditions as $column => $value) {
            if ($value === null || $value === '' || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $record = DB::table($table)->where($column, $value)->first();

            if ($record) {
                return $record;
            }
        }

        return null;
    }

    private function recordRows(string $table, object $record, array $labels): array
    {
        $rows = [];

        foreach ($labels as $column => $label) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $value = $record->{$column} ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if ($column === 'gender') {
                $value = match ((string) $value) {
                    'male', 'laki-laki', 'L' => 'Laki-laki',
                    'female', 'perempuan', 'P' => 'Perempuan',
                    default => $value,
                };
            }

            $rows[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $rows;
    }
}

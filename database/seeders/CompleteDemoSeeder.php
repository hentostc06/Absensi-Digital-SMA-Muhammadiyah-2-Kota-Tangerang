<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompleteDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $passwordGuru = Hash::make('Guru123!');
            $passwordSiswa = Hash::make('Siswa123!');

            User::updateOrCreate(
                ['username' => 'admin'],
                [
                    'name' => 'Administrator Tata Usaha',
                    'email' => 'admin@smamuh2tangerang.sch.id',
                    'password' => Hash::make('Admin123!'),
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );

            User::updateOrCreate(
                ['username' => 'tu'],
                [
                    'name' => 'Petugas Tata Usaha',
                    'email' => 'tu@smamuh2tangerang.sch.id',
                    'password' => Hash::make('Tu123456!'),
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );

            $classRows = [
                ['code' => 'X-1', 'name' => 'X 1', 'grade' => 'X', 'major' => 'Umum'],
                ['code' => 'X-2', 'name' => 'X 2', 'grade' => 'X', 'major' => 'Umum'],
                ['code' => 'XI-M1', 'name' => 'XI MIPA 1', 'grade' => 'XI', 'major' => 'MIPA'],
                ['code' => 'XI-M2', 'name' => 'XI MIPA 2', 'grade' => 'XI', 'major' => 'MIPA'],
                ['code' => 'XII-M1', 'name' => 'XII MIPA 1', 'grade' => 'XII', 'major' => 'MIPA'],
                ['code' => 'XII-M2', 'name' => 'XII MIPA 2', 'grade' => 'XII', 'major' => 'MIPA'],
            ];

            $classes = collect();
            foreach ($classRows as $row) {
                $classes->push(SchoolClass::updateOrCreate(
                    ['code' => $row['code']],
                    $row + ['academic_year' => '2026/2027', 'is_active' => true]
                ));
            }

            $subjectRows = [
                ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam'],
                ['code' => 'PKN', 'name' => 'Pendidikan Pancasila'],
                ['code' => 'BIN', 'name' => 'Bahasa Indonesia'],
                ['code' => 'MTK', 'name' => 'Matematika'],
                ['code' => 'ING', 'name' => 'Bahasa Inggris'],
                ['code' => 'FIS', 'name' => 'Fisika'],
                ['code' => 'KIM', 'name' => 'Kimia'],
                ['code' => 'BIO', 'name' => 'Biologi'],
                ['code' => 'INF', 'name' => 'Informatika'],
                ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani dan Olahraga'],
            ];

            $subjects = collect();
            foreach ($subjectRows as $row) {
                $subjects->put($row['code'], Subject::updateOrCreate(
                    ['code' => $row['code']],
                    ['name' => $row['name'], 'is_active' => true]
                ));
            }

            $teacherRows = [
                ['username' => 'guru.ahmad', 'name' => 'Ahmad Fauzi, S.Pd.I', 'email' => 'ahmad.fauzi@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1001', 'phone' => '081210001001', 'subject' => 'PAI'],
                ['username' => 'guru.siti', 'name' => 'Siti Rahmawati, S.Pd', 'email' => 'siti.rahmawati@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1002', 'phone' => '081210001002', 'subject' => 'PKN'],
                ['username' => 'guru.dedi', 'name' => 'Dedi Kurniawan, S.Pd', 'email' => 'dedi.kurniawan@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1003', 'phone' => '081210001003', 'subject' => 'BIN'],
                ['username' => 'guru.nurul', 'name' => 'Nurul Hidayati, S.Pd', 'email' => 'nurul.hidayati@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1004', 'phone' => '081210001004', 'subject' => 'MTK'],
                ['username' => 'guru.rina', 'name' => 'Rina Marlina, S.Pd', 'email' => 'rina.marlina@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1005', 'phone' => '081210001005', 'subject' => 'ING'],
                ['username' => 'guru.feri', 'name' => 'Feri Setiawan, S.Pd', 'email' => 'feri.setiawan@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1006', 'phone' => '081210001006', 'subject' => 'FIS'],
                ['username' => 'guru.wulan', 'name' => 'Wulan Sari, S.Pd', 'email' => 'wulan.sari@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1007', 'phone' => '081210001007', 'subject' => 'KIM'],
                ['username' => 'guru.andi', 'name' => 'Andi Saputra, S.Pd', 'email' => 'andi.saputra@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1008', 'phone' => '081210001008', 'subject' => 'BIO'],
                ['username' => 'guru.demo', 'name' => 'Selvy Pebrianti, S.Kom', 'email' => 'selvy.pebrianti@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1009', 'phone' => '081210001009', 'subject' => 'INF'],
                ['username' => 'guru.fauzi', 'name' => 'M. Fauzi Irianto, S.Ikom', 'email' => 'fauzi.irianto@smamuh2tangerang.sch.id', 'nbm' => 'NBM-1010', 'phone' => '081210001010', 'subject' => 'PJOK'],
            ];

            $teachersBySubject = collect();
            foreach ($teacherRows as $row) {
                $user = User::updateOrCreate(
                    ['username' => $row['username']],
                    [
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'password' => $passwordGuru,
                        'role' => 'guru',
                        'is_active' => true,
                    ]
                );

                $teacher = Teacher::updateOrCreate(
                    ['user_id' => $user->id],
                    ['niy_nbm' => $row['nbm'], 'phone' => $row['phone']]
                );

                $teachersBySubject->put($row['subject'], $teacher);
            }

            $firstNames = [
                'Ahmad', 'Aisyah', 'Alif', 'Anisa', 'Ardi', 'Aulia', 'Bagas', 'Bella', 'Daffa', 'Dewi',
                'Fahri', 'Farah', 'Fikri', 'Fitri', 'Gilang', 'Hana', 'Iqbal', 'Intan', 'Kevin', 'Laila',
                'M. Rizky', 'Nabila', 'Naufal', 'Nayla', 'Rafi', 'Rania', 'Reza', 'Salsa', 'Syifa', 'Zahra',
            ];
            $lastNames = ['Akbar', 'Ananda', 'Arianto', 'Fadillah', 'Firmansyah', 'Hidayat', 'Kurniawan', 'Maulana', 'Pratama', 'Ramadhan', 'Saputra', 'Setiawan'];

            foreach ($classes as $classIndex => $class) {
                for ($number = 1; $number <= 15; $number++) {
                    $serial = ($classIndex * 15) + $number;
                    $nis = sprintf('2026%02d%03d', $classIndex + 1, $number);
                    $username = $nis;
                    $name = $firstNames[($serial - 1) % count($firstNames)] . ' ' . $lastNames[(intdiv($serial - 1, count($firstNames)) + $classIndex) % count($lastNames)];
                    $gender = $serial % 2 === 0 ? 'P' : 'L';

                    $user = User::updateOrCreate(
                        ['username' => $username],
                        [
                            'name' => $name,
                            'email' => null,
                            'password' => $passwordSiswa,
                            'role' => 'siswa',
                            'is_active' => true,
                        ]
                    );

                    Student::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'nis' => $nis,
                            'school_class_id' => $class->id,
                            'gender' => $gender,
                            'phone' => sprintf('0813%08d', 10000000 + $serial),
                            'address' => 'Kota Tangerang, Banten',
                        ]
                    );
                }
            }

            $demoUser = User::updateOrCreate(
                ['username' => '19221273'],
                [
                    'name' => 'Harnel Aikal Fairuz',
                    'email' => null,
                    'password' => $passwordSiswa,
                    'role' => 'siswa',
                    'is_active' => true,
                ]
            );
            Student::updateOrCreate(
                ['user_id' => $demoUser->id],
                [
                    'nis' => '19221273',
                    'school_class_id' => $classes->firstWhere('code', 'XII-M2')->id,
                    'gender' => 'L',
                    'phone' => '081284300481',
                    'address' => 'Kota Tangerang, Banten',
                ]
            );

            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            $periods = [
                ['07:00:00', '08:20:00'],
                ['08:20:00', '09:40:00'],
                ['10:00:00', '11:20:00'],
                ['11:20:00', '12:40:00'],
                ['13:00:00', '14:20:00'],
                ['14:20:00', '15:40:00'],
            ];
            $subjectCodes = $subjects->keys()->values();

            foreach ($classes as $classIndex => $class) {
                foreach ($days as $dayIndex => $day) {
                    $subjectCode = $subjectCodes[($classIndex * 2 + $dayIndex) % $subjectCodes->count()];
                    $subject = $subjects->get($subjectCode);
                    $teacher = $teachersBySubject->get($subjectCode);
                    [$start, $end] = $periods[$classIndex];

                    Schedule::updateOrCreate(
                        [
                            'teacher_id' => $teacher->id,
                            'school_class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'day_of_week' => $day,
                        ],
                        [
                            'start_time' => $start,
                            'end_time' => $end,
                            'room' => $class->name,
                            'is_active' => true,
                        ]
                    );
                }
            }

            $demoSchedule = Schedule::updateOrCreate(
                [
                    'teacher_id' => $teachersBySubject->get('INF')->id,
                    'school_class_id' => $classes->firstWhere('code', 'XII-M2')->id,
                    'subject_id' => $subjects->get('INF')->id,
                    'day_of_week' => 'Sabtu',
                ],
                [
                    'start_time' => '07:00:00',
                    'end_time' => '08:20:00',
                    'room' => 'Lab Komputer',
                    'is_active' => true,
                ]
            );

            Schedule::query()
                ->where('teacher_id', $teachersBySubject->get('INF')->id)
                ->where('school_class_id', $classes->firstWhere('code', 'XII-M2')->id)
                ->where('subject_id', $subjects->get('INF')->id)
                ->where('day_of_week', 'Senin')
                ->where('id', '!=', $demoSchedule->id)
                ->update(['is_active' => false]);

            $dayMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
            $attendanceRows = [];
            $now = now();

            for ($offset = 14; $offset >= 1; $offset--) {
                $date = now()->subDays($offset)->startOfDay();
                $dayName = $dayMap[$date->dayOfWeekIso] ?? null;

                if ($dayName === null) {
                    continue;
                }

                $schedules = Schedule::query()
                    ->where('day_of_week', $dayName)
                    ->where('is_active', true)
                    ->get();

                foreach ($schedules as $schedule) {
                    $openedAt = Carbon::parse($date->toDateString() . ' ' . $schedule->start_time);
                    $closedAt = Carbon::parse($date->toDateString() . ' ' . $schedule->end_time);
                    $uuid = $this->deterministicUuid('session|' . $schedule->id . '|' . $date->toDateString());

                    $session = AttendanceSession::updateOrCreate(
                        ['uuid' => $uuid],
                        [
                            'schedule_id' => $schedule->id,
                            'teacher_id' => $schedule->teacher_id,
                            'school_class_id' => $schedule->school_class_id,
                            'subject_id' => $schedule->subject_id,
                            'opened_at' => $openedAt,
                            'closed_at' => $closedAt,
                            'status' => 'closed',
                            'late_after_minutes' => 15,
                            'token_version' => 1,
                        ]
                    );

                    $students = Student::query()
                        ->where('school_class_id', $schedule->school_class_id)
                        ->get();

                    foreach ($students as $student) {
                        $seed = sprintf('%u', crc32($student->nis . '|' . $date->toDateString() . '|' . $schedule->id));
                        $roll = ((int) $seed) % 100;

                        if ($roll < 80) {
                            $status = 'hadir';
                        } elseif ($roll < 88) {
                            $status = 'terlambat';
                        } elseif ($roll < 93) {
                            $status = 'izin';
                        } elseif ($roll < 97) {
                            $status = 'sakit';
                        } else {
                            $status = 'alpa';
                        }

                        $isQr = in_array($status, ['hadir', 'terlambat'], true);
                        $minutes = $status === 'terlambat'
                            ? 16 + (((int) $seed) % 12)
                            : 2 + (((int) $seed) % 10);

                        $attendanceRows[] = [
                            'attendance_session_id' => $session->id,
                            'student_id' => $student->id,
                            'status' => $status,
                            'scanned_at' => $isQr ? $openedAt->copy()->addMinutes($minutes) : null,
                            'source' => $isQr ? 'qr' : 'manual',
                            'ip_address' => $isQr ? '127.0.0.1' : null,
                            'user_agent' => $isQr ? 'Data demo otomatis' : null,
                            'notes' => $isQr ? null : 'Data contoh untuk pengujian laporan',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            foreach (array_chunk($attendanceRows, 500) as $chunk) {
                Attendance::upsert(
                    $chunk,
                    ['attendance_session_id', 'student_id'],
                    ['status', 'scanned_at', 'source', 'ip_address', 'user_agent', 'notes', 'updated_at']
                );
            }
        });

        $this->command?->newLine();
        $this->command?->info('DATABASE BERHASIL DIISI');
        $this->command?->line('Admin       : ' . User::where('role', 'admin')->count());
        $this->command?->line('Guru        : ' . Teacher::count());
        $this->command?->line('Siswa       : ' . Student::count());
        $this->command?->line('Kelas       : ' . SchoolClass::count());
        $this->command?->line('Mapel       : ' . Subject::count());
        $this->command?->line('Jadwal      : ' . Schedule::count());
        $this->command?->line('Sesi        : ' . AttendanceSession::count());
        $this->command?->line('Data absensi: ' . Attendance::count());
    }

    private function deterministicUuid(string $value): string
    {
        $hash = md5($value);

        return sprintf(
            '%s-%s-4%s-a%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            substr($hash, 17, 3),
            substr($hash, 20, 12)
        );
    }
}

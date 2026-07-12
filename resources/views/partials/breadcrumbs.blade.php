@php
    $routeName = request()->route()?->getName() ?? '';
    $pageTitle = trim($__env->yieldContent('title', 'Dashboard'));

    $module = null;
    $moduleUrl = null;

    $modules = [
        'dashboard' => ['Dashboard', route('dashboard')],
        'admin.accounts' => ['Kelola Akun', Route::has('admin.accounts.index') ? route('admin.accounts.index') : null],
        'admin.students' => ['Data Siswa', Route::has('admin.students.index') ? route('admin.students.index') : null],
        'admin.teachers' => ['Data Guru', Route::has('admin.teachers.index') ? route('admin.teachers.index') : null],
        'admin.classes' => ['Data Kelas', Route::has('admin.classes.index') ? route('admin.classes.index') : null],
        'admin.subjects' => ['Mata Pelajaran', Route::has('admin.subjects.index') ? route('admin.subjects.index') : null],
        'admin.schedules' => ['Jadwal Pelajaran', Route::has('admin.schedules.index') ? route('admin.schedules.index') : null],
        'admin.reports' => ['Laporan Absensi', Route::has('admin.reports.index') ? route('admin.reports.index') : null],
        'teacher.sessions' => ['Sesi QR Code', Route::has('teacher.sessions.index') ? route('teacher.sessions.index') : null],
        'student.scan' => ['Scan QR Code', Route::has('student.scan') ? route('student.scan') : null],
        'student.history' => ['Riwayat Absensi', Route::has('student.history') ? route('student.history') : null],
    ];

    foreach ($modules as $prefix => [$label, $url]) {
        if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
            $module = $label;
            $moduleUrl = $url;
            break;
        }
    }

    $action = null;

    if (str_ends_with($routeName, '.create')) {
        $action = 'Tambah Data';
    } elseif (str_ends_with($routeName, '.edit')) {
        $action = 'Edit Data';
    } elseif (str_ends_with($routeName, '.show')) {
        $action = $pageTitle ?: 'Detail Data';
    }

    if ($routeName === 'dashboard') {
        $module = 'Dashboard';
        $moduleUrl = route('dashboard');
        $action = null;
    }

    if (! $module) {
        $module = $pageTitle ?: 'Halaman';
    }
@endphp

<nav class="bc-breadcrumbs" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>

    @if ($module && $module !== 'Dashboard')
        <span>/</span>

        @if ($moduleUrl)
            <a href="{{ $moduleUrl }}">{{ $module }}</a>
        @else
            <strong>{{ $module }}</strong>
        @endif
    @endif

    @if ($action && $action !== $module)
        <span>/</span>
        <strong>{{ $action }}</strong>
    @endif
</nav>

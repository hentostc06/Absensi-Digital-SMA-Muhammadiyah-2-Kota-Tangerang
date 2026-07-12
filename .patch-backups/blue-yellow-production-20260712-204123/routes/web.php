<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Student\HistoryController;
use App\Http\Controllers\Student\ScanController;
use App\Http\Controllers\Teacher\ManualAttendanceController;
use App\Http\Controllers\Teacher\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
        Route::patch('/accounts/{account}/toggle', [AccountController::class, 'toggle'])->name('accounts.toggle');
        Route::patch('/accounts/{account}/reset-password', [AccountController::class, 'resetPassword'])->name('accounts.reset-password');
        Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

        Route::resources([
            'students' => StudentController::class,
            'teachers' => TeacherController::class,
            'classes' => SchoolClassController::class,
            'subjects' => SubjectController::class,
            'schedules' => ScheduleController::class,
        ]);

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
        Route::get('/reports/excel', [ReportController::class, 'excel'])->name('reports.excel');
    });

    Route::prefix('guru')->name('teacher.')->middleware('role:guru')->group(function () {
        Route::get('/sesi', [SessionController::class, 'index'])->name('sessions.index');
        Route::post('/sesi', [SessionController::class, 'store'])->name('sessions.store');
        Route::get('/sesi/{session}', [SessionController::class, 'show'])->name('sessions.show');
        Route::get('/sesi/{session}/token', [SessionController::class, 'token'])->name('sessions.token');
        Route::get('/sesi/{session}/kehadiran', [SessionController::class, 'attendance'])->name('sessions.attendance');
        Route::post('/sesi/{session}/tutup', [SessionController::class, 'close'])->name('sessions.close');
        Route::post('/sesi/{session}/manual', [ManualAttendanceController::class, 'store'])->name('sessions.manual');
    });

    Route::prefix('siswa')->name('student.')->middleware('role:siswa')->group(function () {
        Route::get('/scan', [ScanController::class, 'index'])->name('scan');
        Route::post('/scan', [ScanController::class, 'store'])->middleware('throttle:20,1')->name('scan.store');
        Route::get('/riwayat', HistoryController::class)->name('history');
    });
});

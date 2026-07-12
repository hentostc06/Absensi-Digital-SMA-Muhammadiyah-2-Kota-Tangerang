<?php
namespace App\Http\Controllers;
use App\Models\{Attendance,AttendanceSession,SchoolClass,Student,Teacher};
class DashboardController extends Controller { public function __invoke(){ $u=request()->user(); if($u->role==='admin')return view('dashboard.admin',['students'=>Student::count(),'teachers'=>Teacher::count(),'classes'=>SchoolClass::count(),'today'=>Attendance::whereDate('scanned_at',today())->count(),'openSessions'=>AttendanceSession::where('status','open')->count()]); if($u->role==='guru')return redirect()->route('teacher.sessions.index'); return redirect()->route('student.scan'); } }

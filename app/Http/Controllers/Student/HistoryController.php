<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
class HistoryController extends Controller { public function __invoke(){return view('student.history',['items'=>request()->user()->student->attendances()->with(['session.subject','session.teacher.user'])->latest('scanned_at')->paginate(20)]);} }

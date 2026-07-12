<?php
namespace App\Services;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
class ReportService { public function query(array $filters): Builder { return Attendance::query()->with(['student.user','student.schoolClass','session.subject','session.teacher.user'])->when($filters['from']??null,fn($q,$v)=>$q->whereDate('scanned_at','>=',$v))->when($filters['to']??null,fn($q,$v)=>$q->whereDate('scanned_at','<=',$v))->when($filters['class_id']??null,fn($q,$v)=>$q->whereHas('student',fn($s)=>$s->where('school_class_id',$v)))->when($filters['status']??null,fn($q,$v)=>$q->where('status',$v))->latest('scanned_at'); } }

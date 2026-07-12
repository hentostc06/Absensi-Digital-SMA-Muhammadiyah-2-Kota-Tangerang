<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model { protected $fillable=['attendance_session_id','student_id','status','scanned_at','source','ip_address','user_agent','notes']; protected function casts():array{return ['scanned_at'=>'datetime'];} public function session(){return $this->belongsTo(AttendanceSession::class,'attendance_session_id');} public function student(){return $this->belongsTo(Student::class);} }

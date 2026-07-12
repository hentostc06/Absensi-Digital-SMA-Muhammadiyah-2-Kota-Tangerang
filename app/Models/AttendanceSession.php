<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceSession extends Model { protected $fillable=['uuid','schedule_id','teacher_id','school_class_id','subject_id','opened_at','closed_at','status','late_after_minutes','token_version']; protected function casts():array{return ['opened_at'=>'datetime','closed_at'=>'datetime'];} public function schedule(){return $this->belongsTo(Schedule::class);} public function teacher(){return $this->belongsTo(Teacher::class);} public function schoolClass(){return $this->belongsTo(SchoolClass::class);} public function subject(){return $this->belongsTo(Subject::class);} public function attendances(){return $this->hasMany(Attendance::class);} public function isOpen():bool{return $this->status==='open' && !$this->closed_at;} }

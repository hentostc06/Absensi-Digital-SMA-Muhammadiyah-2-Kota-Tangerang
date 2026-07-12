<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Schedule extends Model { protected $fillable=['teacher_id','school_class_id','subject_id','day_of_week','start_time','end_time','room','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function teacher(){return $this->belongsTo(Teacher::class);} public function schoolClass(){return $this->belongsTo(SchoolClass::class);} public function subject(){return $this->belongsTo(Subject::class);} public function sessions(){return $this->hasMany(AttendanceSession::class);} }

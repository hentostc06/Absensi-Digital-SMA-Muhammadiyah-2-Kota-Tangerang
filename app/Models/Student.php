<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Student extends Model { protected $fillable=['user_id','school_class_id','nis','gender','phone','address']; public function user(){return $this->belongsTo(User::class);} public function schoolClass(){return $this->belongsTo(SchoolClass::class);} public function attendances(){return $this->hasMany(Attendance::class);} }

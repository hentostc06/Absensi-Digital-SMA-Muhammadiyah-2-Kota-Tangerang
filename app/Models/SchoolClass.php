<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SchoolClass extends Model { protected $table='school_classes'; protected $fillable=['code','name','grade','major','academic_year','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function students(){return $this->hasMany(Student::class);} public function schedules(){return $this->hasMany(Schedule::class);} }

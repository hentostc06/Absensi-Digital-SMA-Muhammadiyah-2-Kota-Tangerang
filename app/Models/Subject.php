<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model { protected $fillable=['code','name','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function schedules(){return $this->hasMany(Schedule::class);} }

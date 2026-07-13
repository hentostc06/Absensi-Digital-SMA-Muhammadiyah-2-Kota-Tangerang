<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
 use HasFactory, Notifiable;
 protected $fillable=['name','username','email',
        'gender','password','role','is_active'];
 protected $hidden=['password','remember_token'];
 protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean']; }
 public function teacher(){return $this->hasOne(Teacher::class);} public function student(){return $this->hasOne(Student::class);}
 public function isRole(string ...$roles): bool {return in_array($this->role,$roles,true);} }

<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Teacher,User}; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB; use Illuminate\Validation\Rule;
class TeacherController extends Controller {
 public function index(Request $r){$items=Teacher::with('user')->when($r->q,fn($q,$v)=>$q->where('niy_nbm','like',"%$v%")->orWhereHas('user',fn($u)=>$u->where('name','like',"%$v%")))->paginate(15)->withQueryString();return view('admin.teachers.index',compact('items'));}
 public function create(){return view('admin.teachers.form',['teacher'=>new Teacher]);}
 public function store(Request $r){$d=$this->data($r);DB::transaction(function()use($d){$u=User::create(['name'=>$d['name'],'username'=>$d['username'],'password'=>$d['password'],'role'=>'guru','is_active'=>true]);Teacher::create(['user_id'=>$u->id,'niy_nbm'=>$d['niy_nbm'],'phone'=>$d['phone']??null]);});return redirect()->route('admin.teachers.index')->with('success','Data guru berhasil ditambahkan.');}
 public function edit(Teacher $teacher){$teacher->load('user');return view('admin.teachers.form',compact('teacher'));}
 public function update(Request $r,Teacher $teacher){$d=$this->data($r,$teacher);DB::transaction(function()use($d,$teacher){$teacher->user->update(['name'=>$d['name'],'username'=>$d['username'],'is_active'=>$d['is_active']??true]+(!empty($d['password'])?['password'=>$d['password']]:[]));$teacher->update(['niy_nbm'=>$d['niy_nbm'],'phone'=>$d['phone']??null]);});return redirect()->route('admin.teachers.index')->with('success','Data guru berhasil diperbarui.');}
 public function destroy(Teacher $teacher){$teacher->user()->delete();return back()->with('success','Data guru berhasil dihapus.');}
 private function data(Request $r,?Teacher $t=null):array{return $r->validate(['name'=>'required|string|max:100','niy_nbm'=>['required','string','max:30',Rule::unique('teachers','niy_nbm')->ignore($t?->id)],'username'=>['required','string','max:50',Rule::unique('users','username')->ignore($t?->user_id)],'phone'=>'nullable|string|max:20','password'=>[$t?'nullable':'required','confirmed','min:8'],'is_active'=>'nullable|boolean']);}
}

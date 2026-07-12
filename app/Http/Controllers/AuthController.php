<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
 public function show(){return Auth::check()?redirect()->route('dashboard'):view('auth.login');}
 public function login(Request $r){$c=$r->validate(['username'=>'required|string','password'=>'required|string']); if(!Auth::attempt($c,$r->boolean('remember')))return back()->withErrors(['username'=>'Username atau password salah.'])->onlyInput('username'); if(!$r->user()->is_active){Auth::logout();return back()->withErrors(['username'=>'Akun dinonaktifkan oleh admin.']);}$r->session()->regenerate();return redirect()->intended(route('dashboard'));}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
}

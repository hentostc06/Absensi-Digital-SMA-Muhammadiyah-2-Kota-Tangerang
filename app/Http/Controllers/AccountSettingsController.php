<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountSettingsController extends Controller
{
    public function index(Request $request)
    {
        return $this->show($request);
    }

    public function show(Request $request)
    {
        return view('account.settings', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        abort(403, 'Jenis kelamin hanya dapat diubah oleh admin.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}

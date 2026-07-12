<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
        ], [
            'username.required' => 'Username atau NIS wajib diisi.',
            'email.required' => 'Gmail terdaftar wajib diisi.',
            'email.email' => 'Format Gmail tidak valid.',
        ]);

        $email = strtolower(trim($data['email']));
        $username = trim($data['username']);

        $user = User::where('username', $username)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('username', 'email'))
                ->with('forgot_error', 'Username/NIS dan Gmail tidak cocok dengan data akun.');
        }

        if (isset($user->is_active) && ! $user->is_active) {
            return back()
                ->withInput($request->only('username', 'email'))
                ->with('forgot_error', 'Akun sedang nonaktif. Hubungi admin sekolah.');
        }

        $lastResetAt = null;

        if (! empty($user->password_reset_sent_at)) {
            try {
                $lastResetAt = $user->password_reset_sent_at instanceof Carbon
                    ? $user->password_reset_sent_at
                    : Carbon::parse($user->password_reset_sent_at);
            } catch (\Throwable $exception) {
                $lastResetAt = null;
            }
        }

        if ($lastResetAt && $lastResetAt->gt(now()->subMinutes(3))) {
            return back()
                ->withInput($request->only('username', 'email'))
                ->with('forgot_error', 'Password baru sudah dikirim. Tunggu beberapa menit sebelum mencoba lagi.');
        }

        $newPassword = $this->generatePassword();

        $user->forceFill([
            'password' => Hash::make($newPassword),
            'password_reset_sent_at' => now(),
            'remember_token' => Str::random(60),
        ])->save();

        try {
            Mail::raw($this->emailBody($user, $newPassword), function ($message) use ($user, $email) {
                $message->to($email, $user->name ?? $user->username)
                    ->subject('Password Baru Sistem Absensi QR');
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput($request->only('username', 'email'))
                ->with('forgot_error', 'Password baru gagal dikirim. Cek konfigurasi Gmail SMTP server.');
        }

        return back()->with('forgot_success', 'Password baru sudah dikirim ke Gmail terdaftar.');
    }

    private function generatePassword(): string
    {
        return 'ABS-' . strtoupper(Str::random(4)) . '-' . random_int(1000, 9999);
    }

    private function emailBody(User $user, string $newPassword): string
    {
        $name = $user->name ?? $user->username;

        return "Halo {$name},\n\n"
            . "Permintaan lupa password untuk Sistem Absensi QR telah diproses.\n\n"
            . "Username/NIS: {$user->username}\n"
            . "Password baru: {$newPassword}\n\n"
            . "Silakan login menggunakan password baru tersebut, lalu simpan dengan aman.\n\n"
            . "SMA Muhammadiyah 2 Kota Tangerang\n"
            . "Skripsi Harnel Aikal Fairuz - 2026";
    }
}

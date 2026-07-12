<?php

namespace App\Services;

use App\Models\AttendanceSession;
use Carbon\CarbonInterface;
use RuntimeException;

class DynamicQrService
{
    public const INTERVAL_SECONDS = 30;

    public function generate(AttendanceSession $session, ?CarbonInterface $at = null): array
    {
        if (! $session->isOpen()) {
            throw new RuntimeException('Sesi absensi sudah ditutup.');
        }

        $time = $at?->timestamp ?? now()->timestamp;
        $slot = intdiv($time, self::INTERVAL_SECONDS);

        $payload = [
            'sid' => $session->id,
            'uuid' => $session->uuid,
            'slot' => $slot,
            'v' => $session->token_version,
        ];

        $encoded = $this->b64(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $sig = $this->b64(hash_hmac('sha256', $encoded, $this->key(), true));

        return [
            'token' => $encoded.'.'.$sig,
            'expires_in' => self::INTERVAL_SECONDS - ($time % self::INTERVAL_SECONDS),
            'slot' => $slot,
        ];
    }

    public function validate(string $token, AttendanceSession $session, ?CarbonInterface $at = null): bool
    {
        $parts = explode('.', $token);

        if (count($parts) !== 2) {
            return false;
        }

        [$encoded, $sig] = $parts;

        if (! hash_equals($this->b64(hash_hmac('sha256', $encoded, $this->key(), true)), $sig)) {
            return false;
        }

        $payload = json_decode($this->unb64($encoded), true);

        if (! is_array($payload)) {
            return false;
        }

        $time = $at?->timestamp ?? now()->timestamp;
        $current = intdiv($time, self::INTERVAL_SECONDS);
        $slot = (int) ($payload['slot'] ?? -999999);

        return (int) ($payload['sid'] ?? 0) === (int) $session->id
            && ($payload['uuid'] ?? '') === $session->uuid
            && (int) ($payload['v'] ?? -1) === (int) $session->token_version
            && abs($slot - $current) <= 1;
    }

    private function key(): string
    {
        $key = (string) config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return hash('sha256', $key.'|dynamic-attendance-qr', true);
    }

    private function b64(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function unb64(string $value): string
    {
        $pad = str_repeat('=', (4 - strlen($value) % 4) % 4);
        return base64_decode(strtr($value.$pad, '-_', '+/')) ?: '';
    }
}

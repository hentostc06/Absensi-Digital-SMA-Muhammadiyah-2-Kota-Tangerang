<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AttendanceSession extends Model
{
    protected $fillable = [
        'uuid',
        'schedule_id',
        'teacher_id',
        'school_class_id',
        'subject_id',
        'opened_at',
        'closed_at',
        'status',
        'late_after_minutes',
        'session_duration_minutes',
        'token_version',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function durationMinutes(): int
    {
        $minutes = (int) ($this->session_duration_minutes ?? 15);

        return $minutes > 0 ? $minutes : 15;
    }


    public function lateAfterMinutes(): int
    {
        $minutes = (int) ($this->late_after_minutes ?? 5);

        return $minutes > 0 ? $minutes : 5;
    }

    public function expiresAt(): ?Carbon
    {
        $openedAt = $this->opened_at ?: $this->created_at;

        if (! $openedAt) {
            return null;
        }

        return Carbon::parse($openedAt)->addMinutes($this->durationMinutes());
    }

    public function remainingSeconds(): int
    {
        $expiresAt = $this->expiresAt();

        if (! $expiresAt) {
            return 0;
        }

        return max(0, now()->diffInSeconds($expiresAt, false));
    }

    public function hasTimedOut(): bool
    {
        $expiresAt = $this->expiresAt();

        return $expiresAt && now()->greaterThan($expiresAt);
    }

    public function closeBecauseTimedOut(): void
    {
        if (! $this->exists) {
            return;
        }

        if ($this->status !== 'open' || $this->closed_at) {
            return;
        }

        $this->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
            'token_version' => ((int) ($this->token_version ?? 0)) + 1,
        ])->saveQuietly();
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        if ($this->closed_at) {
            return false;
        }

        if ($this->hasTimedOut()) {
            $this->closeBecauseTimedOut();

            return false;
        }

        return true;
    }
}

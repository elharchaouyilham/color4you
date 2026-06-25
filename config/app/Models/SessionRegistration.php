<?php

namespace App\Models;

use App\Enums\SessionRegistrationStatus;
use Database\Factories\SessionRegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionRegistration extends Model
{
    /** @use HasFactory<SessionRegistrationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'drawing_session_id',
        'registered_at',
        'cancelled_at',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function drawingSession(): BelongsTo
    {
        return $this->belongsTo(DrawingSession::class)->withTrashed();
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', SessionRegistrationStatus::Cancelled);
    }

    public function canCancel(): bool
    {
        if ($this->status === SessionRegistrationStatus::Cancelled) {
            return false;
        }

        $session = $this->drawingSession;
        if (!$session || !$session->starts_at) {
            return false;
        }

        return $session->starts_at->gt(now()->addHours(24));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => SessionRegistrationStatus::class,
        ];
    }
}

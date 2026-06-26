<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'reserved_at',
        'pickup_due_at',
        'picked_up_at',
        'returned_at',
        'cancelled_at',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [ReservationStatus::Pending, ReservationStatus::Confirmed]);
    }

    public function scopeOverdue($query)
    {
        return $query->active()
            ->where('pickup_due_at', '<', now())
            ->whereNull('picked_up_at');
    }

    public function canCancel(): bool
    {
        return ($this->status === ReservationStatus::Pending || $this->status === ReservationStatus::Confirmed)
            && is_null($this->picked_up_at);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_at' => 'datetime',
            'pickup_due_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'returned_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => ReservationStatus::class,
        ];
    }
}

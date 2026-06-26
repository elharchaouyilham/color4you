<?php

namespace App\Models;

use App\Enums\DrawingSessionStatus;
use Database\Factories\DrawingSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DrawingSession extends Model implements HasMedia
{
    /** @use HasFactory<DrawingSessionFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trainer_profile_id',
        'category_id',
        'title',
        'slug',
        'description',
        'starts_at',
        'ends_at',
        'capacity',
        'registered_count',
        'price',
        'status',
        'trainer_response_note',
        'trainer_responded_at',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(600)
            ->height(400)
            ->nonQueued();
    }

    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(SessionRegistration::class);
    }

    public function availableSeats(): int
    {
        return max(0, $this->capacity - $this->registered_count);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where('id', $value)->first();
        }
        return $this->where($field ?? 'slug', $value)->first();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeOpen($query)
    {
        return $query->where('status', DrawingSessionStatus::Open);
    }

    public function scopeRecent($query, $limit = 5)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function isFull(): bool
    {
        return $this->registered_count >= $this->capacity;
    }

    public function hasUserRegistered(User $user): bool
    {
        return $this->registrations()
            ->where('user_id', $user->id)
            ->active()
            ->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
            'registered_count' => 'integer',
            'price' => 'decimal:2',
            'trainer_responded_at' => 'datetime',
            'status' => DrawingSessionStatus::class,
        ];
    }
}

<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'name',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function trainerProfile(): HasOne
    {
        return $this->hasOne(TrainerProfile::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function sessionRegistrations(): HasMany
    {
        return $this->hasMany(SessionRegistration::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTrainer(): bool
    {
        return $this->hasRole('trainer');
    }

    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    public function scopeTrainers($query)
    {
        return $query->role('trainer');
    }

    public function scopeClients($query)
    {
        return $query->role('client');
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    protected function name(): Attribute
    {
        return Attribute::make(get: fn () => $this->fullName());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

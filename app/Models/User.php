<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['nom', 'prenom', 'email', 'telephone', 'password', 'status'];
    protected $hidden = ['password', 'remember_token'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function seanceDessins(): HasMany
    {
        return $this->hasMany(SeanceDessin::class, 'formateur_id');
    }

    public function inscriptions(): BelongsToMany
    {
        return $this->belongsToMany(SeanceDessin::class, 'inscription_seance')
                    ->withPivot('present')
                    ->withTimestamps();
    }
}
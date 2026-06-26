<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SeanceDessin extends Model
{
    protected $table = 'seance_dessins';
    protected $fillable = ['name', 'description', 'prix', 'date_heure', 'max_participants', 'status', 'formateur_id'];
    protected $casts = ['date_heure' => 'datetime'];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'inscription_seance')
                    ->withPivot('present')
                    ->withTimestamps();
    }
}
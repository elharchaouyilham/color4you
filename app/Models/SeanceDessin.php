<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SeanceDessin extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'seance_dessins';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'prix',
        'date_heure',
        'max_participants',
        'status',
        'formateur_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_heure' => 'datetime',
            'prix' => 'decimal:2',
            'max_participants' => 'integer',
        ];
    }

    /**
     * Formateur (User) who animates the session.
     */
    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    /**
     * Participants (Users) registered to this session.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'inscription_seance', 'seance_dessin_id', 'user_id')
            ->withPivot('present')
            ->withTimestamps();
    }
}

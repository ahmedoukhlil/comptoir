<?php

namespace App\Models;

use Database\Factories\ClotureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['point_id', 'agent_id', 'date', 'solde_theorique', 'solde_compte', 'ecart'])]
class Cloture extends Model
{
    /** @use HasFactory<ClotureFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ClotureDetail::class);
    }
}

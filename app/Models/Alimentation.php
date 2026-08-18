<?php

namespace App\Models;

use Database\Factories\AlimentationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'point_id', 'operateur_id', 'montant', 'date', 'note'])]
class Alimentation extends Model
{
    /** @use HasFactory<AlimentationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Operateur::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cloture_id', 'operateur_id', 'solde_theorique', 'solde_compte', 'ecart'])]
class ClotureDetail extends Model
{
    public function cloture(): BelongsTo
    {
        return $this->belongsTo(Cloture::class);
    }

    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Operateur::class);
    }
}

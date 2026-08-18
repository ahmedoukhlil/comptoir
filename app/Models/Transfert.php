<?php

namespace App\Models;

use Database\Factories\TransfertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['point_id', 'agent_id', 'operateur_source_id', 'operateur_destination_id', 'montant', 'note'])]
class Transfert extends Model
{
    /** @use HasFactory<TransfertFactory> */
    use HasFactory;

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function operateurSource(): BelongsTo
    {
        return $this->belongsTo(Operateur::class, 'operateur_source_id');
    }

    public function operateurDestination(): BelongsTo
    {
        return $this->belongsTo(Operateur::class, 'operateur_destination_id');
    }
}

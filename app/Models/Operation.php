<?php

namespace App\Models;

use Database\Factories\OperationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'numero_piece', 'uuid_client', 'point_id', 'agent_id', 'operateur_id', 'type', 'montant',
    'commission_calculee', 'client_nom', 'client_telephone', 'client_nni',
    'observation', 'synced', 'cloture_id',
])]
class Operation extends Model
{
    /** @use HasFactory<OperationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'synced' => 'boolean',
        ];
    }

    protected function clientNni(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? decrypt($value) : null,
            set: fn (?string $value) => $value ? encrypt($value) : null,
        );
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Operateur::class);
    }

    public function cloture(): BelongsTo
    {
        return $this->belongsTo(Cloture::class);
    }

    public function estModifiable(): bool
    {
        return $this->cloture_id === null;
    }

    public static function genererNumeroPiece(): string
    {
        $annee = now()->year;
        $dernier = static::where('numero_piece', 'like', "OP-{$annee}-%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('numero_piece');

        $sequence = $dernier
            ? ((int) substr($dernier, -6)) + 1
            : 1;

        return sprintf('OP-%d-%06d', $annee, $sequence);
    }
}

<?php

namespace App\Models;

use Database\Factories\OperateurFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'nom', 'bareme_commission', 'est_cash', 'actif'])]
class Operateur extends Model
{
    /** @use HasFactory<OperateurFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'bareme_commission' => 'array',
            'est_cash' => 'boolean',
        ];
    }

    public function scopeMobileMoney($query)
    {
        return $query->where('est_cash', false);
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeDuTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function calculerCommission(int $montant): int
    {
        foreach ($this->bareme_commission['tranches'] ?? [] as $tranche) {
            $min = $tranche['min'] ?? 0;
            $max = $tranche['max'] ?? null;

            if ($montant >= $min && ($max === null || $montant <= $max)) {
                if (isset($tranche['pourcentage'])) {
                    return (int) round($montant * $tranche['pourcentage'] / 100);
                }

                return (int) ($tranche['montant_fixe'] ?? 0);
            }
        }

        return 0;
    }
}

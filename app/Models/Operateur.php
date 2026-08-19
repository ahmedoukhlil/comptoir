<?php

namespace App\Models;

use Database\Factories\OperateurFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'nom', 'bareme_commission', 'est_cash', 'actif', 'pourcentage_partage_point', 'commission_versee_dans_solde'])]
class Operateur extends Model
{
    /** @use HasFactory<OperateurFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'bareme_commission' => 'array',
            'est_cash' => 'boolean',
            'actif' => 'boolean',
            'pourcentage_partage_point' => 'float',
            'commission_versee_dans_solde' => 'boolean',
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

    /**
     * Frais fixe (en MRU) de la tranche correspondant au montant, tel que
     * défini dans la grille tarifaire de l'opérateur. Ce frais est ensuite
     * réparti entre le point de vente et la banque via
     * pourcentage_partage_point — jamais un pourcentage codé en dur.
     */
    public function calculerFrais(int $montant): int
    {
        foreach ($this->bareme_commission['tranches'] ?? [] as $tranche) {
            $min = $tranche['min'] ?? 0;
            $max = $tranche['max'] ?? null;

            if ($montant >= $min && ($max === null || $montant <= $max)) {
                return (int) ($tranche['frais'] ?? 0);
            }
        }

        return 0;
    }

    /**
     * Répartit le frais fixe entre part point de vente et part banque,
     * selon le pourcentage_partage_point propre à cet opérateur.
     *
     * @return array{frais: int, part_point: int, part_banque: int}
     */
    public function repartirCommission(int $montant): array
    {
        $frais = $this->calculerFrais($montant);
        $partPoint = (int) round($frais * $this->pourcentage_partage_point / 100);

        return [
            'frais' => $frais,
            'part_point' => $partPoint,
            'part_banque' => $frais - $partPoint,
        ];
    }
}

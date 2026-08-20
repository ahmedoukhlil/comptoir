<?php

namespace App\Models;

use Database\Factories\OperateurFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nom', 'bareme_depot', 'bareme_retrait_client', 'bareme_retrait_beneficiaire',
    'est_cash', 'actif',
    'pourcentage_partage_point_depot', 'pourcentage_partage_point_retrait_client', 'pourcentage_partage_point_retrait_beneficiaire',
    'commission_versee_dans_solde',
])]
class Operateur extends Model
{
    /** @use HasFactory<OperateurFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'bareme_depot' => 'array',
            'bareme_retrait_client' => 'array',
            'bareme_retrait_beneficiaire' => 'array',
            'est_cash' => 'boolean',
            'actif' => 'boolean',
            'pourcentage_partage_point_depot' => 'float',
            'pourcentage_partage_point_retrait_client' => 'float',
            'pourcentage_partage_point_retrait_beneficiaire' => 'float',
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

    /**
     * Opérateurs globalement actifs (par le super-admin) ET activés par le
     * tenant donné via la table pivot tenant_operateur.
     */
    public function scopeActifPourTenant($query, int $tenantId)
    {
        return $query->where('actif', true)->whereHas('tenants', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->where('tenant_operateur.actif', true);
        });
    }

    /**
     * Opérateurs rattachés à un tenant (actifs ou non pour lui), pour les
     * écrans de gestion où le propriétaire doit voir/activer chaque
     * opérateur de la plateforme.
     */
    public function scopeDuTenant($query, int $tenantId)
    {
        return $query->whereHas('tenants', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_operateur')->withPivot('actif')->withTimestamps();
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    /**
     * Nom de l'attribut bareme_* correspondant à un type d'opération.
     */
    private function attributBareme(string $typeOperation): string
    {
        return match ($typeOperation) {
            'depot' => 'bareme_depot',
            'retrait' => 'bareme_retrait_client',
            'retrait_beneficiaire' => 'bareme_retrait_beneficiaire',
            default => throw new \InvalidArgumentException("Type d'opération inconnu : {$typeOperation}"),
        };
    }

    /**
     * Nom de l'attribut pourcentage_partage_point_* correspondant à un type
     * d'opération.
     */
    private function attributPourcentagePartagePoint(string $typeOperation): string
    {
        return match ($typeOperation) {
            'depot' => 'pourcentage_partage_point_depot',
            'retrait' => 'pourcentage_partage_point_retrait_client',
            'retrait_beneficiaire' => 'pourcentage_partage_point_retrait_beneficiaire',
            default => throw new \InvalidArgumentException("Type d'opération inconnu : {$typeOperation}"),
        };
    }

    /**
     * Frais fixe (en MRU) de la tranche correspondant au montant, tel que
     * défini dans la grille tarifaire propre au type d'opération (le dépôt
     * est en général gratuit, retrait client et retrait bénéficiaire ont
     * chacun leur propre grille). Ce frais est ensuite réparti entre le
     * point de vente et la banque via pourcentage_partage_point_* — jamais
     * un pourcentage codé en dur.
     */
    public function calculerFrais(int $montant, string $typeOperation): int
    {
        $bareme = $this->{$this->attributBareme($typeOperation)};

        foreach ($bareme['tranches'] ?? [] as $tranche) {
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
     * selon le pourcentage_partage_point propre à cet opérateur et à ce
     * type d'opération.
     *
     * @return array{frais: int, part_point: int, part_banque: int}
     */
    public function repartirCommission(int $montant, string $typeOperation): array
    {
        $frais = $this->calculerFrais($montant, $typeOperation);
        $pourcentage = $this->{$this->attributPourcentagePartagePoint($typeOperation)};
        $partPoint = (int) round($frais * $pourcentage / 100);

        return [
            'frais' => $frais,
            'part_point' => $partPoint,
            'part_banque' => $frais - $partPoint,
        ];
    }
}

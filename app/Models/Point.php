<?php

namespace App\Models;

use Database\Factories\PointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['tenant_id', 'nom', 'localisation'])]
class Point extends Model
{
    /** @use HasFactory<PointFactory> */
    use HasFactory;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function alimentations(): HasMany
    {
        return $this->hasMany(Alimentation::class);
    }

    public function transferts(): HasMany
    {
        return $this->hasMany(Transfert::class);
    }

    public function clotures(): HasMany
    {
        return $this->hasMany(Cloture::class);
    }

    /**
     * Somme de tous les soldes par opérateur (cash + mobile money). Un
     * dépôt/retrait ne fait que déplacer l'argent entre cash et mobile
     * money sans en créer ni en détruire, donc ce total ne varie qu'avec
     * les alimentations et les commissions reversées dans le solde.
     */
    public function soldeCaisse(): int
    {
        return (int) $this->soldesParOperateur()->sum('solde');
    }

    /**
     * Solde de caisse ventilé par opérateur (dont Cash), pour refléter le
     * fait que l'argent n'est pas fongible entre le cash et chaque
     * application mobile money.
     *
     * @return Collection<int, array{operateur: Operateur, solde: int}>
     */
    public function soldesParOperateur(): Collection
    {
        $operateurs = Operateur::query()->duTenant($this->tenant_id)->orderBy('id')->get();

        $alimentations = $this->alimentations()
            ->selectRaw('operateur_id, sum(montant) as total')
            ->groupBy('operateur_id')
            ->pluck('total', 'operateur_id');

        $depots = $this->operations()
            ->where('type', 'depot')
            ->selectRaw('operateur_id, sum(montant) as total')
            ->groupBy('operateur_id')
            ->pluck('total', 'operateur_id');

        $retraits = $this->operations()
            ->whereIn('type', ['retrait', 'retrait_beneficiaire'])
            ->selectRaw('operateur_id, sum(montant) as total')
            ->groupBy('operateur_id')
            ->pluck('total', 'operateur_id');

        $transfertsSortants = $this->transferts()
            ->selectRaw('operateur_source_id, sum(montant) as total')
            ->groupBy('operateur_source_id')
            ->pluck('total', 'operateur_source_id');

        $transfertsEntrants = $this->transferts()
            ->selectRaw('operateur_destination_id, sum(montant) as total')
            ->groupBy('operateur_destination_id')
            ->pluck('total', 'operateur_destination_id');

        // Pour les opérateurs qui reversent la part point de vente de la
        // commission directement sur le solde de l'app (plutôt que hors
        // application), cette part s'ajoute au solde comme si elle avait
        // été créditée automatiquement.
        $commissionsVersees = $this->operations()
            ->selectRaw('operateur_id, sum(commission_part_point) as total')
            ->groupBy('operateur_id')
            ->pluck('total', 'operateur_id');

        // Un dépôt/retrait mobile money a toujours une contrepartie en cash
        // physique dans le sens inverse : lors d'un dépôt, le client donne
        // du cash à l'agent, qui envoie l'équivalent depuis son propre
        // compte mobile money vers celui du client (mobile money -, cash +) ;
        // lors d'un retrait, c'est l'inverse (mobile money +, cash -).
        $totalDepots = (int) $depots->sum();
        $totalRetraits = (int) $retraits->sum();

        return $operateurs->map(function (Operateur $operateur) use (
            $alimentations, $depots, $retraits, $transfertsSortants, $transfertsEntrants, $commissionsVersees,
            $totalDepots, $totalRetraits
        ) {
            $solde = (int) ($alimentations[$operateur->id] ?? 0)
                - (int) ($depots[$operateur->id] ?? 0)
                + (int) ($retraits[$operateur->id] ?? 0)
                - (int) ($transfertsSortants[$operateur->id] ?? 0)
                + (int) ($transfertsEntrants[$operateur->id] ?? 0);

            if ($operateur->commission_versee_dans_solde) {
                $solde += (int) ($commissionsVersees[$operateur->id] ?? 0);
            }

            if ($operateur->est_cash) {
                $solde += $totalDepots - $totalRetraits;
            }

            return ['operateur' => $operateur, 'solde' => $solde];
        });
    }
}

<?php

namespace App\Livewire\Caisse;

use App\Livewire\Concerns\BasculeLangue;
use App\Livewire\Concerns\VerifieLectureSeule;
use App\Models\Operateur;
use App\Models\Operation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Saisie extends Component
{
    use BasculeLangue, VerifieLectureSeule;

    #[Computed]
    public function point()
    {
        return Auth::user()->point;
    }

    #[Computed]
    public function operateurs()
    {
        return Operateur::query()->actifPourTenant($this->point->tenant_id)->mobileMoney()->orderBy('id')->get();
    }

    #[Computed]
    public function operateursPourJs(): array
    {
        return $this->operateurs
            ->map(fn (Operateur $o) => [
                'id' => $o->id,
                'nom' => $o->nom,
                'logoUrl' => $o->logoUrl(),
                'bareme_depot' => $o->bareme_depot,
                'bareme_retrait_client' => $o->bareme_retrait_client,
                'bareme_retrait_beneficiaire' => $o->bareme_retrait_beneficiaire,
                'pourcentage_partage_point_depot' => $o->pourcentage_partage_point_depot,
                'pourcentage_partage_point_retrait_client' => $o->pourcentage_partage_point_retrait_client,
                'pourcentage_partage_point_retrait_beneficiaire' => $o->pourcentage_partage_point_retrait_beneficiaire,
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function soldesParOperateur()
    {
        return $this->point->soldesParOperateur();
    }

    #[Computed]
    public function soldeCash(): ?array
    {
        $ligne = $this->soldesParOperateur->firstWhere('operateur.est_cash', true);

        return $ligne ? ['operateur' => $ligne['operateur'], 'solde' => $ligne['solde']] : null;
    }

    #[Computed]
    public function solde(): int
    {
        return $this->point->soldeCaisse();
    }

    #[Computed]
    public function operationsDuJour()
    {
        return $this->point->operations()
            ->whereDate('created_at', today())
            ->latest()
            ->get();
    }

    #[Computed]
    public function beneficeDuJour(): int
    {
        return (int) $this->operationsDuJour->sum('commission_part_point');
    }

    #[Computed]
    public function guideAAfficher(): bool
    {
        return Auth::user()->guide_vu_le === null;
    }

    public function marquerGuideVu(): void
    {
        Auth::user()->forceFill(['guide_vu_le' => now()])->save();
    }

    public function confirmer(array $champs): array
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            return $erreur;
        }

        $validateur = validator($champs, [
            'type' => ['required', 'in:depot,retrait,retrait_beneficiaire'],
            'operateurId' => ['required', 'integer', 'exists:operateurs,id'],
            'telephone' => ['required', 'digits:8'],
            'clientNom' => ['nullable', 'string'],
            'clientNni' => ['nullable', 'string'],
            'montant' => ['required', 'integer', 'min:1'],
        ], [
            'telephone.digits' => __('caisse.erreur_telephone_digits'),
            'montant.min' => __('caisse.erreur_montant_vide'),
        ]);

        if ($validateur->fails()) {
            return ['erreur' => $validateur->errors()->first()];
        }

        $donnees = $validateur->validated();
        $montant = (int) $donnees['montant'];
        $operateur = $this->operateurs->firstWhere('id', $donnees['operateurId']);

        if (! $operateur) {
            return ['erreur' => __('caisse.erreur_solde_insuffisant')];
        }

        $soldesParOperateur = $this->point->soldesParOperateur();

        if (in_array($donnees['type'], ['retrait', 'retrait_beneficiaire'], true)) {
            $soldeOperateur = $soldesParOperateur->firstWhere('operateur.id', $operateur->id)['solde'] ?? 0;

            if ($montant > $soldeOperateur) {
                return ['erreur' => __('caisse.erreur_solde_insuffisant')];
            }

            // Un retrait fait diminuer le cash (contrepartie physique remise
            // au client) : jamais en dessous de zero, meme si le solde de
            // l'operateur mobile money choisi est suffisant.
            $soldeCash = $soldesParOperateur->firstWhere('operateur.est_cash', true)['solde'] ?? 0;

            if ($montant > $soldeCash) {
                return ['erreur' => __('caisse.erreur_cash_insuffisant')];
            }
        }

        if ($donnees['type'] === 'depot') {
            // Un depot fait diminuer le solde mobile money de l'operateur
            // choisi (l'agent envoie l'equivalent au destinataire) : jamais
            // en dessous de zero.
            $soldeOperateur = $soldesParOperateur->firstWhere('operateur.id', $operateur->id)['solde'] ?? 0;

            if ($montant > $soldeOperateur) {
                return ['erreur' => __('caisse.erreur_solde_insuffisant')];
            }
        }

        DB::transaction(function () use ($operateur, $montant, $donnees) {
            $repartition = $operateur->repartirCommission($montant, $donnees['type']);

            Operation::create([
                'numero_piece' => Operation::genererNumeroPiece(),
                'uuid_client' => (string) Str::uuid(),
                'point_id' => $this->point->id,
                'agent_id' => Auth::id(),
                'operateur_id' => $operateur->id,
                'type' => $donnees['type'],
                'montant' => $montant,
                'commission_calculee' => $repartition['frais'],
                'commission_part_point' => $repartition['part_point'],
                'commission_part_banque' => $repartition['part_banque'],
                'client_nom' => $donnees['clientNom'] ?: null,
                'client_telephone' => $donnees['telephone'],
                'client_nni' => $donnees['clientNni'] ?: null,
                'synced' => true,
            ]);
        });

        unset($this->solde, $this->soldesParOperateur, $this->operationsDuJour, $this->beneficeDuJour);

        return $this->soldesPourJs();
    }

    public function rafraichirApresSynchronisation(): array
    {
        unset($this->solde, $this->soldesParOperateur, $this->operationsDuJour, $this->beneficeDuJour);

        return $this->soldesPourJs();
    }

    private function soldesPourJs(): array
    {
        return [
            'soldes' => $this->soldesParOperateur->pluck('solde', 'operateur.id'),
            'soldeTotal' => $this->solde,
        ];
    }

    public function render()
    {
        return view('livewire.caisse.saisie');
    }
}

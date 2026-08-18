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
        return Operateur::query()->mobileMoney()->orderBy('id')->get();
    }

    #[Computed]
    public function operateursPourJs(): array
    {
        return $this->operateurs
            ->map(fn (Operateur $o) => [
                'id' => $o->id,
                'nom' => $o->nom,
                'bareme_commission' => $o->bareme_commission,
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function soldesParOperateur()
    {
        $soldes = $this->point->soldesParOperateur();

        return $soldes->filter(fn ($ligne) => ! $ligne['operateur']->est_cash)->values();
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
        return (int) $this->operationsDuJour->sum('commission_calculee');
    }

    public function confirmer(array $champs): array
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            return $erreur;
        }

        $validateur = validator($champs, [
            'type' => ['required', 'in:depot,retrait'],
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

        if ($donnees['type'] === 'retrait') {
            $soldeOperateur = $this->point->soldesParOperateur()
                ->firstWhere('operateur.id', $operateur->id)['solde'] ?? 0;

            if ($montant > $soldeOperateur) {
                return ['erreur' => __('caisse.erreur_solde_insuffisant')];
            }
        }

        DB::transaction(function () use ($operateur, $montant, $donnees) {
            Operation::create([
                'numero_piece' => Operation::genererNumeroPiece(),
                'uuid_client' => (string) Str::uuid(),
                'point_id' => $this->point->id,
                'agent_id' => Auth::id(),
                'operateur_id' => $operateur->id,
                'type' => $donnees['type'],
                'montant' => $montant,
                'commission_calculee' => $operateur->calculerCommission($montant),
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

<?php

namespace App\Livewire\Caisse;

use App\Livewire\Concerns\BasculeLangue;
use App\Livewire\Concerns\VerifieLectureSeule;
use App\Models\Cloture as ClotureModel;
use App\Models\ClotureDetail;
use App\Models\Operateur;
use App\Models\Operation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Cloture extends Component
{
    use BasculeLangue, VerifieLectureSeule;

    public bool $confirmationOuverte = false;

    #[Computed]
    public function point()
    {
        return Auth::user()->point;
    }

    #[Computed]
    public function clotureDuJour(): ?ClotureModel
    {
        return $this->point->clotures()->with('details.operateur')->whereDate('date', today())->first();
    }

    #[Computed]
    public function soldesTheoriquesParOperateur()
    {
        return $this->point->soldesParOperateur();
    }

    #[Computed]
    public function soldeTheorique(): int
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

    public function ouvrirConfirmation(): void
    {
        $this->confirmationOuverte = true;
    }

    public function fermerConfirmation(): void
    {
        $this->confirmationOuverte = false;
    }

    /**
     * @param  array<int, int>  $soldesComptes  Solde compté par l'agent, indexé par operateur_id.
     */
    public function cloturer(array $soldesComptes): ?array
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            return $erreur;
        }

        if ($this->clotureDuJour) {
            return null;
        }

        $operateurIds = Operateur::query()->duTenant($this->point->tenant_id)->pluck('id');

        foreach ($operateurIds as $operateurId) {
            if (! isset($soldesComptes[$operateurId]) || $soldesComptes[$operateurId] < 0) {
                return null;
            }
        }

        $point = $this->point;
        $soldesTheoriques = $this->soldesTheoriquesParOperateur;
        $soldeTheoriqueTotal = $this->soldeTheorique;
        $soldeCompteTotal = array_sum($soldesComptes);

        DB::transaction(function () use ($point, $soldesTheoriques, $soldesComptes, $soldeTheoriqueTotal, $soldeCompteTotal) {
            $cloture = ClotureModel::create([
                'point_id' => $point->id,
                'agent_id' => Auth::id(),
                'date' => today(),
                'solde_theorique' => $soldeTheoriqueTotal,
                'solde_compte' => $soldeCompteTotal,
                'ecart' => $soldeCompteTotal - $soldeTheoriqueTotal,
            ]);

            foreach ($soldesTheoriques as $ligne) {
                $operateur = $ligne['operateur'];
                $theorique = $ligne['solde'];
                $compte = $soldesComptes[$operateur->id];

                ClotureDetail::create([
                    'cloture_id' => $cloture->id,
                    'operateur_id' => $operateur->id,
                    'solde_theorique' => $theorique,
                    'solde_compte' => $compte,
                    'ecart' => $compte - $theorique,
                ]);
            }

            Operation::where('point_id', $point->id)
                ->whereDate('created_at', today())
                ->whereNull('cloture_id')
                ->update(['cloture_id' => $cloture->id]);
        });

        $this->confirmationOuverte = false;

        unset($this->clotureDuJour, $this->operationsDuJour, $this->soldeTheorique, $this->soldesTheoriquesParOperateur);

        return null;
    }

    public function render()
    {
        return view('livewire.caisse.cloture');
    }
}

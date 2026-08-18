<?php

namespace App\Livewire\Proprietaire;

use App\Livewire\Concerns\BasculeLangue;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use BasculeLangue;

    /**
     * Heure après laquelle un point sans clôture du jour déclenche une alerte.
     */
    private const HEURE_CLOTURE_ATTENDUE = 20;

    #[Computed]
    public function tenant()
    {
        return Auth::user()->tenant;
    }

    #[Computed]
    public function points()
    {
        return $this->tenant->points()
            ->withCount(['operations as operations_jour_count' => function ($q) {
                $q->whereDate('created_at', today());
            }])
            ->with(['clotures' => function ($q) {
                $q->whereDate('date', today());
            }])
            ->get()
            ->map(function ($point) {
                $cloture = $point->clotures->first();

                return (object) [
                    'point' => $point,
                    'solde' => $point->soldeCaisse(),
                    'soldes_par_operateur' => $point->soldesParOperateur(),
                    'operations_jour' => $point->operations_jour_count,
                    'benefices' => (int) $point->operations()->whereDate('created_at', today())->sum('commission_calculee'),
                    'cloture' => $cloture,
                    'a_ecart' => $cloture && $cloture->ecart !== 0,
                    'cloture_manquante' => ! $cloture && now()->hour >= self::HEURE_CLOTURE_ATTENDUE,
                ];
            });
    }

    #[Computed]
    public function soldeTotal(): int
    {
        return (int) $this->points->sum('solde');
    }

    #[Computed]
    public function operationsJourTotal(): int
    {
        return (int) $this->points->sum('operations_jour');
    }

    #[Computed]
    public function beneficesTotal(): int
    {
        return (int) $this->points->sum('benefices');
    }

    public function render()
    {
        return view('livewire.proprietaire.dashboard');
    }
}

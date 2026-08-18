<?php

namespace App\Livewire\Caisse;

use App\Livewire\Concerns\BasculeLangue;
use App\Livewire\Concerns\VerifieLectureSeule;
use App\Models\Cloture as ClotureModel;
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

    #[Computed]
    public function point()
    {
        return Auth::user()->point;
    }

    #[Computed]
    public function clotureDuJour(): ?ClotureModel
    {
        return $this->point->clotures()->whereDate('date', today())->first();
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

    public function cloturer(int $soldeCompte): ?array
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            return $erreur;
        }

        if ($this->clotureDuJour) {
            return null;
        }

        if ($soldeCompte < 0) {
            return null;
        }

        $soldeTheorique = $this->soldeTheorique;
        $point = $this->point;

        DB::transaction(function () use ($point, $soldeTheorique, $soldeCompte) {
            $cloture = ClotureModel::create([
                'point_id' => $point->id,
                'agent_id' => Auth::id(),
                'date' => today(),
                'solde_theorique' => $soldeTheorique,
                'solde_compte' => $soldeCompte,
                'ecart' => $soldeCompte - $soldeTheorique,
            ]);

            Operation::where('point_id', $point->id)
                ->whereDate('created_at', today())
                ->whereNull('cloture_id')
                ->update(['cloture_id' => $cloture->id]);
        });

        unset($this->clotureDuJour, $this->operationsDuJour, $this->soldeTheorique);

        return null;
    }

    public function render()
    {
        return view('livewire.caisse.cloture');
    }
}

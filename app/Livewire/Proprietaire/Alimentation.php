<?php

namespace App\Livewire\Proprietaire;

use App\Livewire\Concerns\BasculeLangue;
use App\Livewire\Concerns\VerifieLectureSeule;
use App\Models\Alimentation as AlimentationModel;
use App\Models\Operateur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Alimentation extends Component
{
    use BasculeLangue, VerifieLectureSeule;

    #[Computed]
    public function tenant()
    {
        return Auth::user()->tenant;
    }

    #[Computed]
    public function points()
    {
        return $this->tenant->points()->orderBy('nom')->get();
    }

    #[Computed]
    public function operateurs()
    {
        return Operateur::query()->actifPourTenant($this->tenant->id)->orderBy('id')->get();
    }

    #[Computed]
    public function operateursPourJs(): array
    {
        return $this->operateurs
            ->map(fn (Operateur $o) => ['id' => $o->id, 'nom' => $o->nom, 'estCash' => $o->est_cash])
            ->values()
            ->all();
    }

    #[Computed]
    public function alimentationsRecentes()
    {
        return AlimentationModel::whereIn('point_id', $this->points->pluck('id'))
            ->with(['point', 'operateur'])
            ->latest()
            ->limit(15)
            ->get();
    }

    /**
     * @param  array<int, int>  $montantsParOperateur  [operateur_id => montant]
     */
    public function alimenter(int $pointId, array $montantsParOperateur, ?string $note): array
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            return $erreur;
        }

        if (! $this->points->contains('id', $pointId)) {
            return ['erreur' => 'Point invalide.'];
        }

        $montantsParOperateur = array_filter($montantsParOperateur, fn ($m) => (int) $m > 0);

        if (empty($montantsParOperateur)) {
            return ['erreur' => __('caisse.erreur_montant_vide')];
        }

        DB::transaction(function () use ($pointId, $montantsParOperateur, $note) {
            foreach ($montantsParOperateur as $operateurId => $montant) {
                AlimentationModel::create([
                    'tenant_id' => $this->tenant->id,
                    'point_id' => $pointId,
                    'operateur_id' => $operateurId,
                    'montant' => (int) $montant,
                    'date' => today(),
                    'note' => $note ?: null,
                ]);
            }
        });

        unset($this->alimentationsRecentes);

        return ['ok' => true];
    }

    public function render()
    {
        return view('livewire.proprietaire.alimentation');
    }
}

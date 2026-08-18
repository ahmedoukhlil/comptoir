<?php

namespace App\Livewire\Proprietaire;

use App\Livewire\Concerns\BasculeLangue;
use App\Livewire\Concerns\VerifieLectureSeule;
use App\Models\Operateur;
use App\Models\Point;
use App\Models\Transfert as TransfertModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Transfert extends Component
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
        return Operateur::query()->orderBy('id')->get();
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
    public function transfertsRecents()
    {
        return TransfertModel::whereIn('point_id', $this->points->pluck('id'))
            ->with(['point', 'operateurSource', 'operateurDestination'])
            ->latest()
            ->limit(15)
            ->get();
    }

    public function soldesDuPoint(int $pointId): array
    {
        $point = $this->points->firstWhere('id', $pointId);

        if (! $point) {
            return [];
        }

        return $point->soldesParOperateur()
            ->pluck('solde', 'operateur.id')
            ->all();
    }

    public function transferer(int $pointId, int $sourceId, int $destinationId, int $montant, ?string $note): array
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            return $erreur;
        }

        $point = $this->points->firstWhere('id', $pointId);

        if (! $point) {
            return ['erreur' => 'Point invalide.'];
        }

        if ($sourceId === $destinationId) {
            return ['erreur' => __('caisse.erreur_transfert_meme_operateur')];
        }

        if ($montant <= 0) {
            return ['erreur' => __('caisse.erreur_montant_vide')];
        }

        $soldeSource = $point->soldesParOperateur()->firstWhere('operateur.id', $sourceId)['solde'] ?? 0;

        if ($montant > $soldeSource) {
            return ['erreur' => __('caisse.erreur_solde_insuffisant')];
        }

        DB::transaction(function () use ($point, $sourceId, $destinationId, $montant, $note) {
            TransfertModel::create([
                'point_id' => $point->id,
                'agent_id' => Auth::id(),
                'operateur_source_id' => $sourceId,
                'operateur_destination_id' => $destinationId,
                'montant' => $montant,
                'note' => $note ?: null,
            ]);
        });

        unset($this->transfertsRecents);

        return ['ok' => true];
    }

    public function render()
    {
        return view('livewire.proprietaire.transfert');
    }
}

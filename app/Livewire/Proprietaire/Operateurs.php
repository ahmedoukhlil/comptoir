<?php

namespace App\Livewire\Proprietaire;

use App\Livewire\Concerns\BasculeLangue;
use App\Livewire\Concerns\VerifieLectureSeule;
use App\Models\Operateur;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Operateurs extends Component
{
    use BasculeLangue, VerifieLectureSeule;

    #[Computed]
    public function tenant()
    {
        return Auth::user()->tenant;
    }

    #[Computed]
    public function operateurs()
    {
        return Operateur::query()
            ->duTenant($this->tenant->id)
            ->where('actif', true)
            ->with(['tenants' => fn ($q) => $q->where('tenant_id', $this->tenant->id)])
            ->orderBy('nom')
            ->get();
    }

    public function basculerActif(int $operateurId): void
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            $this->addError('lectureSeule', $erreur['erreur']);

            return;
        }

        $operateur = Operateur::where('actif', true)
            ->whereHas('tenants', fn ($q) => $q->where('tenant_id', $this->tenant->id))
            ->whereKey($operateurId)
            ->firstOrFail();

        $actifActuel = (bool) $operateur->tenants->first()?->pivot->actif;

        $this->tenant->operateurs()->syncWithoutDetaching([
            $operateurId => ['actif' => ! $actifActuel],
        ]);

        unset($this->operateurs);
    }

    public function render()
    {
        return view('livewire.proprietaire.operateurs');
    }
}

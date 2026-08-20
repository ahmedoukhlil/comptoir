<?php

namespace App\Livewire\Admin\Operateurs;

use App\Models\Operateur;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    #[Computed]
    public function operateurs()
    {
        return Operateur::query()->orderByDesc('actif')->orderBy('nom')->get();
    }

    public function basculerActif(int $operateurId): void
    {
        $operateur = Operateur::findOrFail($operateurId);

        $operateur->update(['actif' => ! $operateur->actif]);

        unset($this->operateurs);
    }

    public function render()
    {
        return view('livewire.admin.operateurs.index');
    }
}

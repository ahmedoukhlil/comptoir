<?php

namespace App\Livewire\Caisse;

use App\Livewire\Concerns\BasculeLangue;
use App\Models\Operateur;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Historique extends Component
{
    use BasculeLangue, WithPagination;

    #[Url]
    public ?int $operateurId = null;

    #[Url]
    public string $type = '';

    #[Url]
    public string $recherche = '';

    public function updating($nom): void
    {
        if (in_array($nom, ['operateurId', 'type', 'recherche'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function point()
    {
        return Auth::user()->point;
    }

    #[Computed]
    public function operateurs()
    {
        return Operateur::query()->duTenant($this->point->tenant_id)->orderBy('id')->get();
    }

    #[Computed]
    public function operations()
    {
        return $this->point->operations()
            ->with('operateur')
            ->when($this->operateurId, fn ($q) => $q->where('operateur_id', $this->operateurId))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->recherche, fn ($q) => $q->where('client_telephone', 'like', '%'.$this->recherche.'%'))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function totalEntrees(): int
    {
        return (int) $this->baseFiltree()->where('type', 'depot')->sum('montant');
    }

    #[Computed]
    public function totalSorties(): int
    {
        return (int) $this->baseFiltree()->whereIn('type', ['retrait', 'retrait_beneficiaire'])->sum('montant');
    }

    protected function baseFiltree()
    {
        return $this->point->operations()
            ->when($this->operateurId, fn ($q) => $q->where('operateur_id', $this->operateurId))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->recherche, fn ($q) => $q->where('client_telephone', 'like', '%'.$this->recherche.'%'));
    }

    public function render()
    {
        return view('livewire.caisse.historique');
    }
}

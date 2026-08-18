<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $recherche = '';

    #[Url]
    public string $plan = '';

    #[Url]
    public string $statut = '';

    public function updating($nom): void
    {
        if (in_array($nom, ['recherche', 'plan', 'statut'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::query()
            ->withCount(['points', 'agents'])
            ->when($this->recherche, fn ($q) => $q->where('nom', 'like', '%'.$this->recherche.'%'))
            ->when($this->plan, fn ($q) => $q->where('plan', $this->plan))
            ->when($this->statut, fn ($q) => $q->where('statut', $this->statut))
            ->latest()
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.tenants.index');
    }
}

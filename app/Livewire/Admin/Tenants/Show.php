<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{

    public int $tenantId;

    public function mount(int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    #[Computed]
    public function tenant(): Tenant
    {
        return Tenant::with(['points.agents'])->findOrFail($this->tenantId);
    }

    public function changerPlan(string $plan): void
    {
        if (! in_array($plan, ['solo', 'reseau', 'entreprise'], true)) {
            return;
        }

        $this->tenant->update(['plan' => $plan]);

        unset($this->tenant);
    }

    public function changerStatut(string $statut): void
    {
        if (! in_array($statut, ['essai', 'actif', 'lecture_seule', 'suspendu'], true)) {
            return;
        }

        $this->tenant->update(['statut' => $statut]);

        unset($this->tenant);
    }

    public function render()
    {
        return view('livewire.admin.tenants.show');
    }
}

<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{

    public int $tenantId;

    public bool $confirmationSuppression = false;

    public ?int $agentAReinitialiserId = null;

    public ?string $motDePasseGenere = null;

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

    public function supprimer(): void
    {
        $tenant = $this->tenant;

        $tenant->agents()->delete();
        $tenant->delete();

        $this->redirectRoute('admin.tenants.index');
    }

    public function ouvrirConfirmationSuppression(): void
    {
        $this->confirmationSuppression = true;
    }

    public function fermerConfirmationSuppression(): void
    {
        $this->confirmationSuppression = false;
    }

    public function ouvrirConfirmationReinitialisation(int $agentId): void
    {
        $this->agentAReinitialiserId = $agentId;
        $this->motDePasseGenere = null;
    }

    #[Computed]
    public function agentAReinitialiser(): ?User
    {
        if (! $this->agentAReinitialiserId) {
            return null;
        }

        return User::find($this->agentAReinitialiserId);
    }

    public function fermerConfirmationReinitialisation(): void
    {
        $this->agentAReinitialiserId = null;
        $this->motDePasseGenere = null;
    }

    public function reinitialiserMotDePasse(): void
    {
        if (! $this->agentAReinitialiserId) {
            return;
        }

        $agent = User::whereKey($this->agentAReinitialiserId)
            ->where('tenant_id', $this->tenantId)
            ->firstOrFail();

        $motDePasse = Str::password(10, symbols: false);

        $agent->update(['password' => $motDePasse]);

        $this->motDePasseGenere = $motDePasse;
    }

    public function render()
    {
        return view('livewire.admin.tenants.show');
    }
}

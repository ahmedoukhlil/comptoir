<?php

namespace App\Livewire\Admin\Points;

use App\Models\Point;
use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Create extends Component
{
    public int $tenantId;

    public string $nom = '';

    public string $localisation = '';

    public bool $cree = false;

    public function mount(int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    #[Computed]
    public function tenant(): Tenant
    {
        return Tenant::findOrFail($this->tenantId);
    }

    public function creer(): void
    {
        $this->validate([
            'nom' => ['required', 'string', 'max:255'],
            'localisation' => ['nullable', 'string', 'max:255'],
        ]);

        Point::create([
            'tenant_id' => $this->tenant->id,
            'nom' => $this->nom,
            'localisation' => $this->localisation ?: null,
        ]);

        $this->reset(['nom', 'localisation']);
        $this->cree = true;
    }

    public function render()
    {
        return view('livewire.admin.points.create');
    }
}

<?php

namespace App\Livewire\Admin\Agents;

use App\Models\Point;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Create extends Component
{
    public int $pointId;

    public string $nom = '';

    public string $telephone = '';

    public string $role = 'agent';

    public ?string $motDePasseGenere = null;

    public function mount(int $pointId): void
    {
        $this->pointId = $pointId;
    }

    #[Computed]
    public function point(): Point
    {
        return Point::with('tenant')->findOrFail($this->pointId);
    }

    public function creer(): void
    {
        $this->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'digits:8', 'unique:users,telephone'],
            'role' => ['required', 'in:agent,proprietaire'],
        ]);

        $motDePasse = Str::password(10, symbols: false);

        User::create([
            'tenant_id' => $this->point->tenant_id,
            'point_id' => $this->point->id,
            'name' => $this->nom,
            'telephone' => $this->telephone,
            'password' => $motDePasse,
            'role' => $this->role,
        ]);

        $this->motDePasseGenere = $motDePasse;
        $this->reset(['nom', 'telephone', 'role']);
        $this->role = 'agent';
    }

    public function render()
    {
        return view('livewire.admin.agents.create');
    }
}

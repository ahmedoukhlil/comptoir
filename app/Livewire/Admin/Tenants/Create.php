<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Create extends Component
{
    public string $tenantNom = '';

    public string $plan = 'solo';

    public string $pointNom = '';

    public string $pointLocalisation = '';

    public string $proprietaireNom = '';

    public string $proprietaireTelephone = '';

    public ?string $motDePasseGenere = null;

    public function creer(): void
    {
        $this->validate([
            'tenantNom' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'in:solo,reseau,entreprise'],
            'pointNom' => ['required', 'string', 'max:255'],
            'pointLocalisation' => ['nullable', 'string', 'max:255'],
            'proprietaireNom' => ['required', 'string', 'max:255'],
            'proprietaireTelephone' => ['required', 'digits:8', 'unique:users,telephone'],
        ]);

        $motDePasse = Str::password(10, symbols: false);

        DB::transaction(function () use ($motDePasse) {
            $tenant = Tenant::create([
                'nom' => $this->tenantNom,
                'plan' => $this->plan,
                'statut' => 'actif',
            ]);

            $point = Point::create([
                'tenant_id' => $tenant->id,
                'nom' => $this->pointNom,
                'localisation' => $this->pointLocalisation ?: null,
            ]);

            User::create([
                'tenant_id' => $tenant->id,
                'point_id' => $point->id,
                'name' => $this->proprietaireNom,
                'telephone' => $this->proprietaireTelephone,
                'password' => $motDePasse,
                'role' => 'proprietaire',
            ]);
        });

        $this->motDePasseGenere = $motDePasse;
        $this->reset(['tenantNom', 'plan', 'pointNom', 'pointLocalisation', 'proprietaireNom', 'proprietaireTelephone']);
        $this->plan = 'solo';
    }

    public function render()
    {
        return view('livewire.admin.tenants.create');
    }
}

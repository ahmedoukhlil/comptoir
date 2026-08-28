<?php

namespace App\Livewire\Proprietaire;

use App\Livewire\Concerns\BasculeLangue;
use App\Livewire\Concerns\VerifieLectureSeule;
use App\Models\Point;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Agents extends Component
{
    use BasculeLangue, VerifieLectureSeule;

    public ?int $pointId = null;

    public string $nom = '';

    public string $telephone = '';

    public string $role = 'agent';

    public ?string $motDePasseGenere = null;

    public function mount(): void
    {
        $this->pointId = $this->points->first()?->id;
    }

    #[Computed]
    public function guideAAfficher(): bool
    {
        return Auth::user()->guide_vu_le === null;
    }

    public function marquerGuideVu(): void
    {
        Auth::user()->forceFill(['guide_vu_le' => now()])->save();
    }

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
    public function agents()
    {
        return User::whereIn('point_id', $this->points->pluck('id'))
            ->where('role', '!=', 'super_admin')
            ->with('point')
            ->orderBy('name')
            ->get();
    }

    public function creer(): void
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            $this->addError('lectureSeule', $erreur['erreur']);

            return;
        }

        $this->validate([
            'pointId' => ['required', 'integer'],
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'digits:8', 'unique:users,telephone'],
            'role' => ['required', 'in:agent,proprietaire'],
        ]);

        $point = $this->points->firstWhere('id', $this->pointId);

        if (! $point) {
            $this->addError('pointId', __('caisse.erreur_point_invalide'));

            return;
        }

        $motDePasse = Str::password(10, symbols: false);

        User::create([
            'tenant_id' => $this->tenant->id,
            'point_id' => $point->id,
            'name' => $this->nom,
            'telephone' => $this->telephone,
            'password' => $motDePasse,
            'role' => $this->role,
        ]);

        $this->motDePasseGenere = $motDePasse;
        $this->reset(['nom', 'telephone', 'role']);
        $this->role = 'agent';
        unset($this->agents);
    }

    public function render()
    {
        return view('livewire.proprietaire.agents');
    }
}

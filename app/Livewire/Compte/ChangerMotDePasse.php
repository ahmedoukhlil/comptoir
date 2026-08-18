<?php

namespace App\Livewire\Compte;

use App\Livewire\Concerns\BasculeLangue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangerMotDePasse extends Component
{
    use BasculeLangue;

    public string $motDePasseActuel = '';

    public string $nouveauMotDePasse = '';

    public string $nouveauMotDePasse_confirmation = '';

    public bool $reussi = false;

    public function changer(): void
    {
        $this->reussi = false;

        $this->validate([
            'motDePasseActuel' => ['required', 'string'],
            'nouveauMotDePasse' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $utilisateur = Auth::user();

        if (! Hash::check($this->motDePasseActuel, $utilisateur->password)) {
            $this->addError('motDePasseActuel', $utilisateur->estSuperAdmin()
                ? __('admin.erreur_mot_de_passe_actuel')
                : __('caisse.erreur_mot_de_passe_actuel'));

            return;
        }

        $utilisateur->update(['password' => $this->nouveauMotDePasse]);

        $this->reset(['motDePasseActuel', 'nouveauMotDePasse', 'nouveauMotDePasse_confirmation']);
        $this->reussi = true;
    }

    public function render()
    {
        $estSuperAdmin = Auth::user()->estSuperAdmin();

        return view('livewire.compte.changer-mot-de-passe', ['estSuperAdmin' => $estSuperAdmin])
            ->layout($estSuperAdmin ? 'layouts.admin' : 'layouts.app');
    }
}

<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\BasculeLangue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    use BasculeLangue;

    public string $telephone = '';

    public string $password = '';

    public string $error = '';

    public function seConnecter()
    {
        $this->error = '';

        $this->validate([
            'telephone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $cle = 'login:'.$this->telephone;

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            $this->error = __('auth.erreur_trop_de_tentatives');

            return;
        }

        if (! Auth::attempt(['telephone' => $this->telephone, 'password' => $this->password])) {
            RateLimiter::hit($cle, 60);
            $this->error = __('auth.erreur_identifiants');

            return;
        }

        RateLimiter::clear($cle);
        session()->regenerate();

        return redirect()->route('accueil');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

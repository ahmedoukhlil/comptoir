<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait VerifieLectureSeule
{
    protected function refuserSiLectureSeule(): ?array
    {
        if (Auth::user()?->tenant?->enLectureSeule()) {
            return ['erreur' => __('caisse.erreur_lecture_seule')];
        }

        return null;
    }
}

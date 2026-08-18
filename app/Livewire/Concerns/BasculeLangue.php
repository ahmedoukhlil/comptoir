<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\App;

trait BasculeLangue
{
    public function changerLangue(string $locale): void
    {
        if (! in_array($locale, ['fr', 'ar'], true)) {
            return;
        }

        session(['locale' => $locale]);
        App::setLocale($locale);

        $this->dispatch('langue-changee', locale: $locale);
    }
}

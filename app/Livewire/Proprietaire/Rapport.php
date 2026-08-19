<?php

namespace App\Livewire\Proprietaire;

use App\Livewire\Concerns\BasculeLangue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Rapport extends Component
{
    use BasculeLangue;

    #[Url]
    public string $periode = 'jour';

    #[Computed]
    public function tenant()
    {
        return Auth::user()->tenant;
    }

    #[Computed]
    public function bornes(): array
    {
        return match ($this->periode) {
            'semaine' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'mois' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };
    }

    #[Computed]
    public function lignes()
    {
        [$debut, $fin] = $this->bornes;

        return $this->tenant->points()->orderBy('nom')->get()->map(function ($point) use ($debut, $fin) {
            $capital = (int) $point->alimentations()->whereBetween('date', [$debut, $fin])->sum('montant');
            $commissions = (int) $point->operations()->whereBetween('created_at', [$debut, $fin])->sum('commission_part_point');
            $commissionsBanque = (int) $point->operations()->whereBetween('created_at', [$debut, $fin])->sum('commission_part_banque');

            return (object) [
                'point' => $point,
                'capital' => $capital,
                'commissions' => $commissions,
                'commissions_banque' => $commissionsBanque,
            ];
        });
    }

    #[Computed]
    public function totalCapital(): int
    {
        return (int) $this->lignes->sum('capital');
    }

    #[Computed]
    public function totalCommissions(): int
    {
        return (int) $this->lignes->sum('commissions');
    }

    #[Computed]
    public function totalCommissionsBanque(): int
    {
        return (int) $this->lignes->sum('commissions_banque');
    }

    #[Computed]
    public function totalOperations(): int
    {
        [$debut, $fin] = $this->bornes;

        return (int) $this->tenant->points()
            ->withCount(['operations' => fn ($q) => $q->whereBetween('created_at', [$debut, $fin])])
            ->get()
            ->sum('operations_count');
    }

    public function render()
    {
        return view('livewire.proprietaire.rapport');
    }
}

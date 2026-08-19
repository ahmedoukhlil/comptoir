<div class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <div class="mx-auto max-w-[980px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-start justify-between">
                    <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white">{{ __('caisse.rapport_titre') }}</span>
                    <x-selecteur-langue />
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                {{-- Sélecteur de période --}}
                <div class="flex gap-2 mb-6">
                    <button type="button" wire:click="$set('periode', 'jour')" class="text-xs font-semibold px-4 py-2.5 rounded-lg border-[1.5px] {{ $periode === 'jour' ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.rapport_periode_jour') }}</button>
                    <button type="button" wire:click="$set('periode', 'semaine')" class="text-xs font-semibold px-4 py-2.5 rounded-lg border-[1.5px] {{ $periode === 'semaine' ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.rapport_periode_semaine') }}</button>
                    <button type="button" wire:click="$set('periode', 'mois')" class="text-xs font-semibold px-4 py-2.5 rounded-lg border-[1.5px] {{ $periode === 'mois' ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.rapport_periode_mois') }}</button>
                </div>

                {{-- Exports --}}
                <div class="flex gap-2 mb-6">
                    <a href="{{ route('proprietaire.rapport.export.excel', ['periode' => $periode]) }}" class="text-xs font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-green-deep)] text-white flex items-center gap-1.5">📊 {{ __('caisse.historique_exporter_excel') }}</a>
                    <a href="{{ route('proprietaire.rapport.export.pdf', ['periode' => $periode]) }}" class="text-xs font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-rust-deep)] text-white flex items-center gap-1.5">📄 {{ __('caisse.historique_exporter_pdf') }}</a>
                </div>

                {{-- Résumé --}}
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-[color:var(--color-paper)] rounded-xl p-4 border border-[color:var(--color-line)]">
                        <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase">{{ __('caisse.rapport_capital_injecte') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-xl text-[color:var(--color-ink)] mt-1">{{ number_format($this->totalCapital, 0, ',', ' ') }}</div>
                    </div>
                    <div class="bg-[color:var(--color-green)]/10 rounded-xl p-4">
                        <div class="text-[11px] font-semibold text-[color:var(--color-green-deep)] uppercase">{{ __('caisse.rapport_commissions') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-xl text-[color:var(--color-green-deep)] mt-1">+ {{ number_format($this->totalCommissions, 0, ',', ' ') }}</div>
                    </div>
                </div>

                {{-- Détail par point --}}
                <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                    @foreach ($this->lignes as $ligne)
                        <div class="flex items-center justify-between py-3">
                            <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-sm text-[color:var(--color-ink)]">{{ $ligne->point->nom }}</div>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="text-[color:var(--color-ink-soft)]">{{ __('caisse.rapport_capital_injecte') }} : <b class="text-[color:var(--color-ink)]">{{ number_format($ligne->capital, 0, ',', ' ') }}</b></span>
                                <span class="text-[color:var(--color-green-deep)] font-semibold">+ {{ number_format($ligne->commissions, 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-8">
                    <a href="{{ route('proprietaire.dashboard') }}" class="text-sm font-semibold text-[color:var(--color-ink-soft)] underline">
                        {{ __('caisse.retour_dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <div class="mx-auto max-w-[980px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div id="guide-cible-rapport-bandeau" class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-bouton-retour :href="route('proprietaire.dashboard')" />
                        <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white truncate">{{ __('caisse.rapport_titre') }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-selecteur-langue />
                        <button
                            type="button"
                            x-data
                            x-on:click="window.dispatchEvent(new CustomEvent('guide:relancer', { detail: { groupe: 'rapport' } }))"
                            class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            aria-label="{{ __('caisse.guide_revoir') }}"
                        >?</button>
                    </div>
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                {{-- Sélecteur de période --}}
                <div id="guide-cible-rapport-periode" class="flex gap-2 mb-6">
                    <button type="button" wire:click="$set('periode', 'jour')" class="text-xs font-semibold px-4 py-2.5 rounded-lg border-[1.5px] {{ $periode === 'jour' ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.rapport_periode_jour') }}</button>
                    <button type="button" wire:click="$set('periode', 'semaine')" class="text-xs font-semibold px-4 py-2.5 rounded-lg border-[1.5px] {{ $periode === 'semaine' ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.rapport_periode_semaine') }}</button>
                    <button type="button" wire:click="$set('periode', 'mois')" class="text-xs font-semibold px-4 py-2.5 rounded-lg border-[1.5px] {{ $periode === 'mois' ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.rapport_periode_mois') }}</button>
                </div>

                {{-- Exports --}}
                <div id="guide-cible-rapport-exports" class="flex gap-2 mb-6">
                    <a href="{{ route('proprietaire.rapport.export.excel', ['periode' => $periode]) }}" class="text-xs font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-green-deep)] text-white flex items-center gap-1.5"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/></svg> {{ __('caisse.historique_exporter_excel') }}</a>
                    <a href="{{ route('proprietaire.rapport.export.pdf', ['periode' => $periode]) }}" class="text-xs font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-rust-deep)] text-white flex items-center gap-1.5"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg> {{ __('caisse.historique_exporter_pdf') }}</a>
                </div>

                {{-- Résumé --}}
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-[color:var(--color-paper)] rounded-xl p-4 border border-[color:var(--color-line)]">
                        <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase">{{ __('caisse.rapport_capital_injecte') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-xl text-[color:var(--color-ink)] mt-1">{{ number_format($this->totalCapital, 0, ',', ' ') }}</div>
                    </div>
                    <div class="bg-[color:var(--color-green)]/10 rounded-xl p-4">
                        <div class="text-[11px] font-semibold text-[color:var(--color-green-deep)] uppercase">{{ __('caisse.rapport_commissions') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-xl text-[color:var(--color-green-deep)] mt-1" dir="ltr">+ {{ number_format($this->totalCommissions, 0, ',', ' ') }}</div>
                    </div>
                </div>

                <div class="text-[11px] text-[color:var(--color-ink-soft)] mb-6">
                    {{ __('caisse.rapport_commissions_banque') }} : <span dir="ltr">{{ number_format($this->totalCommissionsBanque, 0, ',', ' ') }}</span>
                </div>

                {{-- Détail par point --}}
                <div id="guide-cible-rapport-detail" class="divide-y divide-dashed divide-[color:var(--color-line)]">
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
            </div>
        </div>
    </div>

    <x-guide-decouverte
        groupe="rapport"
        :visible-initial="$this->guideAAfficher"
        :etapes="[
            ['cible' => '#guide-cible-rapport-bandeau', 'texte' => __('caisse.guide_rapport_1')],
            ['cible' => '#guide-cible-rapport-periode', 'texte' => __('caisse.guide_rapport_2')],
            ['cible' => '#guide-cible-rapport-exports', 'texte' => __('caisse.guide_rapport_3')],
            ['cible' => '#guide-cible-rapport-detail', 'texte' => __('caisse.guide_rapport_4')],
        ]"
        wire-termine="$wire.marquerGuideVu()"
    />
</div>

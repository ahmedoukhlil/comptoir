<div class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <div class="mx-auto max-w-[980px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-bouton-retour :href="route('caisse.saisie')" />
                        <div class="min-w-0">
                            <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white truncate">{{ __('caisse.historique_titre') }}</span>
                            <b class="block text-sm font-semibold mt-0.5 text-white truncate">{{ $this->point->nom }}</b>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <x-selecteur-langue />
                        <button
                            type="button"
                            x-data
                            x-on:click="window.dispatchEvent(new CustomEvent('guide:relancer', { detail: { groupe: 'historique' } }))"
                            class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            aria-label="{{ __('caisse.guide_revoir') }}"
                        >?</button>
                    </div>
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                {{-- Filtres --}}
                <div id="guide-cible-historique-filtres" class="mb-4">
                    <div class="text-[11px] font-semibold tracking-wide text-[color:var(--color-ink-soft)] uppercase mb-1.5">{{ __('caisse.historique_operateur') }}</div>
                    <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('caisse.historique_operateur') }}">
                        <button
                            type="button"
                            role="radio"
                            aria-checked="{{ ! $operateurId ? 'true' : 'false' }}"
                            wire:click="$set('operateurId', null)"
                            class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ ! $operateurId ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}"
                        >{{ __('caisse.historique_tous') }}</button>
                        @foreach ($this->operateurs as $operateur)
                            <button
                                type="button"
                                role="radio"
                                aria-checked="{{ $operateurId === $operateur->id ? 'true' : 'false' }}"
                                wire:click="$set('operateurId', {{ $operateur->id }})"
                                class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ $operateurId === $operateur->id ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}"
                            >{{ $operateur->nom }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-[11px] font-semibold tracking-wide text-[color:var(--color-ink-soft)] uppercase mb-1.5">{{ __('caisse.historique_type') }}</div>
                    <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('caisse.historique_type') }}">
                        <button type="button" role="radio" aria-checked="{{ $type === '' ? 'true' : 'false' }}" wire:click="$set('type', '')" class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ $type === '' ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.historique_tous') }}</button>
                        <button type="button" role="radio" aria-checked="{{ $type === 'depot' ? 'true' : 'false' }}" wire:click="$set('type', 'depot')" class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ $type === 'depot' ? 'border-[color:var(--color-green)] bg-[color:var(--color-green)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.depot') }}</button>
                        <button type="button" role="radio" aria-checked="{{ $type === 'retrait' ? 'true' : 'false' }}" wire:click="$set('type', 'retrait')" class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ $type === 'retrait' ? 'border-[color:var(--color-rust)] bg-[color:var(--color-rust)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.retrait') }}</button>
                        <button type="button" role="radio" aria-checked="{{ $type === 'retrait_beneficiaire' ? 'true' : 'false' }}" wire:click="$set('type', 'retrait_beneficiaire')" class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ $type === 'retrait_beneficiaire' ? 'border-[color:var(--color-rust)] bg-[color:var(--color-rust)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.retrait_beneficiaire') }}</button>
                    </div>
                </div>

                <div id="guide-cible-historique-recherche" class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-xl px-4 py-2.5 mb-4 max-w-sm">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-ink-soft)] flex-shrink-0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input
                        type="tel"
                        wire:model.live.debounce.400ms="recherche"
                        inputmode="numeric"
                        placeholder="{{ __('caisse.historique_recherche') }}"
                        dir="ltr"
                        class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)] text-start"
                    >
                </div>

                {{-- Exports --}}
                <div id="guide-cible-historique-exports" class="flex gap-2 mb-6">
                    <a
                        href="{{ route('caisse.historique.export.excel', ['operateurId' => $operateurId, 'type' => $type, 'recherche' => $recherche]) }}"
                        class="text-xs font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-green-deep)] text-white flex items-center gap-1.5"
                    ><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/></svg> {{ __('caisse.historique_exporter_excel') }}</a>
                    <a
                        href="{{ route('caisse.historique.export.pdf', ['operateurId' => $operateurId, 'type' => $type, 'recherche' => $recherche]) }}"
                        class="text-xs font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-rust-deep)] text-white flex items-center gap-1.5"
                    ><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg> {{ __('caisse.historique_exporter_pdf') }}</a>
                </div>

                {{-- Résumé --}}
                <div id="guide-cible-historique-resume" class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-[color:var(--color-green)]/10 rounded-xl p-4">
                        <div class="text-[11px] font-semibold text-[color:var(--color-green-deep)] uppercase">{{ __('caisse.colonne_entrees') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-green-deep)] mt-1 text-start" dir="ltr">+ {{ number_format($this->totalEntrees, 0, ',', ' ') }} <span class="text-xs font-semibold">{{ __('caisse.devise') }}</span></div>
                    </div>
                    <div class="bg-[color:var(--color-rust)]/10 rounded-xl p-4">
                        <div class="text-[11px] font-semibold text-[color:var(--color-rust-deep)] uppercase">{{ __('caisse.colonne_sorties') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-rust-deep)] mt-1 text-start" dir="ltr">− {{ number_format($this->totalSorties, 0, ',', ' ') }} <span class="text-xs font-semibold">{{ __('caisse.devise') }}</span></div>
                    </div>
                </div>

                {{-- Liste --}}
                <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                    @forelse ($this->operations as $operation)
                        <div class="flex items-center gap-3 py-3">
                            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $operation->type === 'depot' ? 'bg-[color:var(--color-green)]' : 'bg-[color:var(--color-rust)]' }}"></div>
                            <div class="flex-1">
                                <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-sm font-bold text-[color:var(--color-ink)]">
                                    {{ $operation->libelleType() }} · {{ $operation->operateur->nom }}
                                </div>
                                <div class="text-[11px] text-[color:var(--color-ink-soft)] mt-0.5 text-start" dir="ltr">
                                    {{ $operation->created_at->format('d/m/Y H:i') }} · {{ $operation->numero_piece }} · {{ $operation->client_telephone }}
                                </div>
                            </div>
                            <div class="font-[family-name:var(--font-mono)] font-bold text-[15px] text-end {{ $operation->type === 'depot' ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]' }}" dir="ltr">
                                {{ $operation->type === 'depot' ? '+' : '−' }} {{ number_format($operation->montant, 0, ',', ' ') }} <span class="text-xs font-semibold">{{ __('caisse.devise') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[color:var(--color-ink-soft)] py-8 text-center">{{ __('caisse.historique_aucun_resultat') }}</p>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $this->operations->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-guide-decouverte
        groupe="historique"
        :visible-initial="$this->guideAAfficher"
        :etapes="[
            ['cible' => '#guide-cible-historique-filtres', 'texte' => __('caisse.guide_historique_1')],
            ['cible' => '#guide-cible-historique-recherche', 'texte' => __('caisse.guide_historique_2')],
            ['cible' => '#guide-cible-historique-exports', 'texte' => __('caisse.guide_historique_3')],
            ['cible' => '#guide-cible-historique-resume', 'texte' => __('caisse.guide_historique_4')],
        ]"
        wire-termine="$wire.marquerGuideVu()"
    />
</div>

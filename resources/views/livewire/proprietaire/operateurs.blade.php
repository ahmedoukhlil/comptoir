<div class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <div class="mx-auto max-w-[600px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-start justify-between">
                    <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white">{{ __('caisse.operateurs_titre') }}</span>
                    <x-selecteur-langue />
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-3 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.operateurs_nouveau') }}</div>

                <form wire:submit="creer" class="space-y-4">
                    <div>
                        <label for="operateur-nom" class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.operateur_nom_label') }}</label>
                        <input id="operateur-nom" type="text" wire:model="nom" placeholder="{{ __('caisse.operateur_nom_placeholder') }}" class="w-full rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                        @error('nom') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1.5">{{ __('caisse.operateur_type_label') }}</span>
                        <div class="flex gap-1.5" role="group" aria-label="{{ __('caisse.operateur_type_label') }}">
                            <button
                                type="button"
                                wire:click="$set('estCash', false)"
                                aria-pressed="{{ $estCash ? 'false' : 'true' }}"
                                class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] {{ ! $estCash ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                            ><x-icone-type-operateur :est-cash="false" width="16" height="16" class="inline-block align-text-bottom" /> {{ __('caisse.operateur_type_mobile') }}</button>
                            <button
                                type="button"
                                wire:click="$set('estCash', true)"
                                aria-pressed="{{ $estCash ? 'true' : 'false' }}"
                                class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] {{ $estCash ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                            ><x-icone-type-operateur :est-cash="true" width="16" height="16" class="inline-block align-text-bottom" /> {{ __('caisse.operateur_type_cash') }}</button>
                        </div>
                    </div>

                    @unless ($estCash)
                        <div>
                            <div class="flex gap-1.5 mb-3" role="tablist" aria-label="{{ __('caisse.operateur_onglets_bareme') }}">
                                @foreach (['depot', 'retrait', 'retrait_beneficiaire'] as $type)
                                    <button
                                        type="button"
                                        role="tab"
                                        aria-selected="{{ $onglet === $type ? 'true' : 'false' }}"
                                        wire:click="changerOnglet('{{ $type }}')"
                                        class="flex-1 text-xs font-semibold px-2 py-2 rounded-lg border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] {{ $onglet === $type ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                                    >{{ __('caisse.'.$type) }}</button>
                                @endforeach
                            </div>

                            @foreach (['depot', 'retrait', 'retrait_beneficiaire'] as $type)
                                <div @if ($onglet !== $type) style="display:none" @endif>
                                    <x-proprietaire.tranches-operateur :tranches="$tranchesParType[$type]" :type="$type" />
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <label for="operateur-partage" class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.operateur_partage_label') }}</label>
                            <p id="operateur-partage-aide" class="text-[11px] text-[color:var(--color-ink-soft)] mb-1.5">{{ __('caisse.operateur_partage_aide') }}</p>
                            <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-lg px-3.5 py-2.5">
                                <input id="operateur-partage" type="number" step="0.1" min="0" max="100" inputmode="decimal" dir="ltr" aria-describedby="operateur-partage-aide" wire:model="pourcentagePartagePoint" class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)] font-[family-name:var(--font-mono)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                                <span class="text-sm text-[color:var(--color-ink-soft)]">%</span>
                            </div>
                            @error('pourcentagePartagePoint') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model="commissionVerseeDansSolde" aria-describedby="operateur-versee-aide" class="mt-0.5 w-4 h-4 rounded border-[color:var(--color-line)] text-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                            <span id="operateur-versee-aide" class="text-xs text-[color:var(--color-ink-soft)]">{{ __('caisse.operateur_versee_dans_solde_aide') }}</span>
                        </label>
                    @endunless

                    @error('lectureSeule') <p class="text-xs text-[color:var(--color-rust-deep)]">{{ $message }}</p> @enderror

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-[color:var(--color-ink)] text-white font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold py-3 mt-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                    >{{ __('caisse.creer') }}</button>
                </form>

                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mt-8 mb-3 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.operateurs_liste_titre') }}</div>
                <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                    @forelse ($this->operateurs as $operateur)
                        <div class="flex items-center justify-between py-2.5 {{ ! $operateur->actif ? 'opacity-50' : '' }}">
                            <div class="flex items-center gap-2.5">
                                <x-icone-type-operateur :est-cash="$operateur->est_cash" width="17" height="17" class="text-[color:var(--color-ink-soft)] flex-shrink-0" />
                                <div>
                                    <div class="text-sm font-semibold text-[color:var(--color-ink)]">{{ $operateur->nom }}</div>
                                    @unless ($operateur->est_cash)
                                        <div class="text-[11px] text-[color:var(--color-ink-soft)]" dir="ltr">
                                            {{ count($operateur->bareme_retrait_client['tranches'] ?? []) }} {{ __('caisse.operateur_tranches_compte') }} · {{ $operateur->pourcentage_partage_point }}%
                                            @if (empty($operateur->bareme_retrait_beneficiaire['tranches'] ?? []))
                                                · <span class="text-[#8C6A1F]">{{ __('caisse.operateur_beneficiaire_non_defini') }}</span>
                                            @endif
                                            @if ($operateur->commission_versee_dans_solde)
                                                · <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block align-text-bottom" aria-label="{{ __('caisse.operateur_versee_dans_solde_aide') }}"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>
                                            @endif
                                        </div>
                                    @endunless
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                @unless ($operateur->est_cash)
                                    <button
                                        type="button"
                                        wire:click="ouvrirModification({{ $operateur->id }})"
                                        aria-label="{{ __('caisse.operateur_modifier') }} {{ $operateur->nom }}"
                                        class="text-[11px] font-semibold px-2.5 py-1.5 min-h-[24px] rounded-md bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                                    >{{ __('caisse.operateur_modifier') }}</button>
                                @endunless
                                <button
                                    type="button"
                                    wire:click="basculerActif({{ $operateur->id }})"
                                    aria-label="{{ ($operateur->actif ? __('caisse.operateur_desactiver') : __('caisse.operateur_activer')) . ' ' . $operateur->nom }}"
                                    class="text-[11px] font-semibold px-2.5 py-1.5 min-h-[24px] rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 {{ $operateur->actif ? 'bg-[color:var(--color-rust)]/10 text-[color:var(--color-rust-deep)] focus-visible:outline-[color:var(--color-rust-deep)]' : 'bg-[color:var(--color-green)]/10 text-[color:var(--color-green-deep)] focus-visible:outline-[color:var(--color-green-deep)]' }}"
                                >{{ $operateur->actif ? __('caisse.operateur_desactiver') : __('caisse.operateur_activer') }}</button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[color:var(--color-ink-soft)] py-4 text-center">{{ __('caisse.operateurs_aucun') }}</p>
                    @endforelse
                </div>

                <div class="text-center mt-8">
                    <a href="{{ route('proprietaire.dashboard') }}" class="text-sm font-semibold text-[color:var(--color-ink-soft)] underline">
                        {{ __('caisse.retour_dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if ($operateurAModifierId)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8" role="dialog" aria-modal="true" aria-labelledby="operateur-modif-titre">
            <div class="absolute inset-0 bg-[color:var(--color-ink)]/60 backdrop-blur-sm" wire:click="fermerModification"></div>

            <div class="relative bg-[color:var(--color-paper)] rounded-2xl shadow-xl w-full max-w-sm overflow-hidden max-h-full flex flex-col {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
                <form wire:submit="modifier" class="p-6 overflow-y-auto space-y-4">
                    <div id="operateur-modif-titre" class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-[color:var(--color-ink)]">
                        {{ __('caisse.operateur_modifier_titre') }} — {{ $this->operateurAModifier?->nom }}
                    </div>

                    <div>
                        <div class="flex gap-1.5 mb-3" role="tablist" aria-label="{{ __('caisse.operateur_onglets_bareme') }}">
                            @foreach (['depot', 'retrait', 'retrait_beneficiaire'] as $type)
                                <button
                                    type="button"
                                    role="tab"
                                    aria-selected="{{ $onglet === $type ? 'true' : 'false' }}"
                                    wire:click="changerOnglet('{{ $type }}')"
                                    class="flex-1 text-xs font-semibold px-2 py-2 rounded-lg border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] {{ $onglet === $type ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                                >{{ __('caisse.'.$type) }}</button>
                            @endforeach
                        </div>

                        @foreach (['depot', 'retrait', 'retrait_beneficiaire'] as $type)
                            <div @if ($onglet !== $type) style="display:none" @endif>
                                <x-proprietaire.tranches-operateur :tranches="$tranchesParType[$type]" :type="$type" />
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label for="operateur-modif-partage" class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.operateur_partage_label') }}</label>
                        <p id="operateur-modif-partage-aide" class="text-[11px] text-[color:var(--color-ink-soft)] mb-1.5">{{ __('caisse.operateur_partage_aide') }}</p>
                        <div class="flex items-center gap-2.5 bg-[color:var(--color-sand-deep)] border-[1.5px] border-[color:var(--color-line)] rounded-lg px-3.5 py-2.5">
                            <input id="operateur-modif-partage" type="number" step="0.1" min="0" max="100" inputmode="decimal" dir="ltr" aria-describedby="operateur-modif-partage-aide" wire:model="pourcentagePartagePoint" class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)] font-[family-name:var(--font-mono)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                            <span class="text-sm text-[color:var(--color-ink-soft)]">%</span>
                        </div>
                        @error('pourcentagePartagePoint') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" wire:model="commissionVerseeDansSolde" aria-describedby="operateur-modif-versee-aide" class="mt-0.5 w-4 h-4 rounded border-[color:var(--color-line)] text-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                        <span id="operateur-modif-versee-aide" class="text-xs text-[color:var(--color-ink-soft)]">{{ __('caisse.operateur_versee_dans_solde_aide') }}</span>
                    </label>

                    @error('lectureSeule') <p class="text-xs text-[color:var(--color-rust-deep)]">{{ $message }}</p> @enderror
                </form>

                <div class="flex border-t border-[color:var(--color-line)] flex-shrink-0">
                    <button
                        type="button"
                        wire:click="fermerModification"
                        class="flex-1 text-sm font-semibold py-3.5 text-[color:var(--color-ink-soft)] hover:bg-[color:var(--color-sand-deep)] focus-visible:outline focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                    >{{ __('caisse.annuler') }}</button>
                    <button
                        type="button"
                        wire:click="modifier"
                        wire:loading.attr="disabled"
                        class="flex-1 text-sm font-semibold py-3.5 text-white bg-[color:var(--color-ink)] hover:opacity-90 border-s border-[color:var(--color-line)] disabled:opacity-60 focus-visible:outline focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-white"
                    >{{ __('caisse.enregistrer') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>

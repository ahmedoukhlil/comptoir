<div
    x-data="{
        pointId: {{ Js::from($this->points->first()?->id) }},
        operateurs: {{ Js::from($this->operateursPourJs) }},
        montants: {{ Js::from($this->operateurs->mapWithKeys(fn ($o) => [$o->id => ''])) }},
        note: '',
        message: '',
        erreur: '',
        enConfirmation: false,
        confirmationOuverte: false,
        get total() {
            return Object.values(this.montants).reduce((s, v) => s + (parseInt(v || '0', 10)), 0);
        },
        get lignesSaisies() {
            return this.operateurs
                .filter(o => parseInt(this.montants[o.id] || '0', 10) > 0)
                .map(o => ({ nom: o.nom, montant: parseInt(this.montants[o.id], 10) }));
        },
        formaterMontant(v) { return Number(v || 0).toLocaleString('fr-FR').replace(/,/g, ' '); },
        ouvrirConfirmation() {
            if (this.total <= 0) return;
            this.confirmationOuverte = true;
        },
        async confirmer() {
            if (this.total <= 0 || this.enConfirmation) return;
            this.message = ''; this.erreur = '';
            this.enConfirmation = true;
            try {
                const montantsANombre = Object.fromEntries(
                    Object.entries(this.montants).map(([id, v]) => [id, parseInt(v || '0', 10)])
                );
                const resultat = await this.$wire.alimenter(this.pointId, montantsANombre, this.note);
                if (resultat?.erreur) {
                    this.erreur = resultat.erreur;
                    return;
                }
                for (const id in this.montants) this.montants[id] = '';
                this.note = '';
                this.message = @js(__('caisse.alimentation_reussie'));
            } finally {
                this.enConfirmation = false;
                this.confirmationOuverte = false;
            }
        },
    }"
    class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
>
    <div class="mx-auto max-w-[600px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div id="guide-cible-alimentation-bandeau" class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-bouton-retour :href="route('proprietaire.dashboard')" />
                        <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white truncate">{{ __('caisse.alimentation_titre') }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-selecteur-langue />
                        <button
                            type="button"
                            x-on:click="window.dispatchEvent(new CustomEvent('guide:relancer', { detail: { groupe: 'alimentation' } }))"
                            class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            aria-label="{{ __('caisse.guide_revoir') }}"
                        >?</button>
                    </div>
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                <div id="guide-cible-alimentation-point" class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.alimentation_point_label') }}</div>
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach ($this->points as $point)
                        <button
                            type="button"
                            x-on:click="pointId = {{ $point->id }}"
                            class="text-sm font-bold px-4 py-3 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]"
                            :class="pointId === {{ $point->id }} ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                        >{{ $point->nom }}</button>
                    @endforeach
                </div>

                <div id="guide-cible-alimentation-montants" class="flex flex-col gap-2.5">
                    @foreach ($this->operateurs as $operateur)
                        <div class="flex items-center gap-3 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-xl px-4 py-3">
                            <div class="w-24 flex-shrink-0 flex items-center gap-1.5 text-sm font-bold text-[color:var(--color-ink)] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">
                                @if ($operateur->logoUrl())
                                    <img
                                        src="{{ $operateur->logoUrl() }}"
                                        alt="{{ $operateur->nom }}"
                                        class="h-5 max-w-[80px] object-contain"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"
                                    >
                                    <span class="hidden items-center gap-1.5">
                                        <x-icone-type-operateur :est-cash="$operateur->est_cash" width="15" height="15" class="flex-shrink-0 text-[color:var(--color-ink-soft)]" />
                                        {{ $operateur->nom }}
                                    </span>
                                @else
                                    <x-icone-type-operateur :est-cash="$operateur->est_cash" width="15" height="15" class="flex-shrink-0 text-[color:var(--color-ink-soft)]" />
                                    {{ $operateur->nom }}
                                @endif
                            </div>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                x-model="montants[{{ $operateur->id }}]"
                                placeholder="0"
                                dir="ltr"
                                class="flex-1 border-none bg-transparent outline-none font-[family-name:var(--font-mono)] text-lg font-bold text-[color:var(--color-ink)] text-end"
                            >
                            <span class="text-xs text-[color:var(--color-ink-soft)]">{{ __('caisse.devise') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between mt-4 px-1">
                    <span class="text-xs font-semibold text-[color:var(--color-ink-soft)] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.colonne_total') }}</span>
                    <span class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-ink)]" x-text="formaterMontant(total) + ' {{ __('caisse.devise') }}'"></span>
                </div>

                <div class="mt-4">
                    <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.alimentation_note_label') }}</div>
                    <input
                        type="text"
                        x-model="note"
                        placeholder="{{ __('caisse.alimentation_note_placeholder') }}"
                        class="w-full bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-xl px-4 py-3 text-sm font-semibold text-[color:var(--color-ink)] outline-none"
                    >
                </div>

                <button
                    id="guide-cible-alimentation-confirmer"
                    type="button"
                    x-on:click="ouvrirConfirmation()"
                    :disabled="enConfirmation || total <= 0"
                    class="w-full mt-4 flex rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white items-center justify-center gap-2 bg-[color:var(--color-ink)] shadow-lg disabled:opacity-60"
                >
                    {{ __('caisse.alimentation_confirmer') }}
                </button>
                <p x-show="message" x-cloak x-text="message" class="text-sm text-center mt-3 font-semibold text-[color:var(--color-green-deep)]"></p>
                <p x-show="erreur" x-cloak x-text="erreur" class="text-sm text-center mt-3 font-semibold text-[color:var(--color-rust-deep)]"></p>

                @if ($this->alimentationsRecentes->isNotEmpty())
                    <div class="mt-8">
                        <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.alimentation_historique') }}</div>
                        <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                            @foreach ($this->alimentationsRecentes as $alim)
                                <div class="flex items-center justify-between py-2.5">
                                    <div>
                                        <div class="text-sm font-semibold text-[color:var(--color-ink)]">{{ $alim->point->nom }} — {{ $alim->operateur?->nom }}</div>
                                        <div class="text-[11px] text-[color:var(--color-ink-soft)]">{{ $alim->date->format('d/m/Y') }} @if($alim->note) · {{ $alim->note }} @endif</div>
                                    </div>
                                    <div class="font-[family-name:var(--font-mono)] font-bold text-sm text-[color:var(--color-ink)]" dir="ltr">+ {{ number_format($alim->montant, 0, ',', ' ') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Modale de confirmation --}}
            <div x-show="confirmationOuverte" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-[color:var(--color-ink)]/60 backdrop-blur-sm" x-on:click="confirmationOuverte = false"></div>

                <div
                    x-show="confirmationOuverte"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="relative bg-[color:var(--color-paper)] rounded-2xl shadow-xl w-full max-w-sm overflow-hidden {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
                >
                    <div class="p-6">
                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-[color:var(--color-ink)] mb-1.5">
                            {{ __('caisse.confirmation_titre') }}
                        </div>
                        <p class="text-sm text-[color:var(--color-ink-soft)] mb-4">
                            {{ __('caisse.confirmation_texte') }}
                        </p>

                        <div class="divide-y divide-dashed divide-[color:var(--color-line)] border-y border-dashed border-[color:var(--color-line)]">
                            <template x-for="ligne in lignesSaisies" :key="ligne.nom">
                                <div class="flex items-center justify-between py-2.5">
                                    <span class="text-sm font-semibold text-[color:var(--color-ink)]" x-text="ligne.nom"></span>
                                    <span class="text-sm font-[family-name:var(--font-mono)] font-bold text-[color:var(--color-ink)]" dir="ltr" x-text="formaterMontant(ligne.montant)"></span>
                                </div>
                            </template>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm font-bold text-[color:var(--color-ink)]">{{ __('caisse.colonne_total') }}</span>
                                <span class="text-lg font-[family-name:var(--font-mono)] font-bold text-[color:var(--color-ink)]" dir="ltr" x-text="formaterMontant(total)"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex border-t border-[color:var(--color-line)]">
                        <button
                            type="button"
                            x-on:click="confirmationOuverte = false"
                            class="flex-1 text-sm font-semibold py-3.5 text-[color:var(--color-ink-soft)] hover:bg-[color:var(--color-sand-deep)]"
                        >{{ __('caisse.confirmation_annuler') }}</button>
                        <button
                            type="button"
                            x-on:click="confirmer()"
                            :disabled="enConfirmation"
                            class="flex-1 text-sm font-semibold py-3.5 text-white bg-[color:var(--color-ink)] hover:opacity-90 border-s border-[color:var(--color-line)] disabled:opacity-60"
                        ><span x-show="! enConfirmation">{{ __('caisse.confirmation_valider') }}</span><span x-show="enConfirmation" x-cloak>…</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-guide-decouverte
        groupe="alimentation"
        :visible-initial="$this->guideAAfficher"
        :etapes="[
            ['cible' => '#guide-cible-alimentation-bandeau', 'texte' => __('caisse.guide_alimentation_1')],
            ['cible' => '#guide-cible-alimentation-point', 'texte' => __('caisse.guide_alimentation_2')],
            ['cible' => '#guide-cible-alimentation-montants', 'texte' => __('caisse.guide_alimentation_3')],
            ['cible' => '#guide-cible-alimentation-confirmer', 'texte' => __('caisse.guide_alimentation_4')],
        ]"
        wire-termine="$wire.marquerGuideVu()"
    />
</div>

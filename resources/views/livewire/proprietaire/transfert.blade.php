<div
    x-data="{
        pointId: {{ Js::from($this->points->first()?->id) }},
        operateurs: {{ Js::from($this->operateursPourJs) }},
        soldes: {},
        sourceId: {{ Js::from($this->operateurs->first()?->id) }},
        destinationId: {{ Js::from($this->operateurs->skip(1)->first()?->id) }},
        montant: '',
        note: '',
        message: '',
        erreur: '',
        enConfirmation: false,
        confirmationOuverte: false,
        formaterMontant(v) { return Number(v || 0).toLocaleString('fr-FR').replace(/,/g, ' '); },
        async chargerSoldes() {
            this.soldes = await this.$wire.soldesDuPoint(this.pointId);
        },
        ouvrirConfirmation() {
            if (this.montant === '' || parseInt(this.montant, 10) <= 0 || this.sourceId === this.destinationId) return;
            this.confirmationOuverte = true;
        },
        async confirmer() {
            if (this.montant === '' || this.enConfirmation) return;
            this.message = ''; this.erreur = '';
            this.enConfirmation = true;
            try {
                const resultat = await this.$wire.transferer(this.pointId, this.sourceId, this.destinationId, parseInt(this.montant, 10), this.note);
                if (resultat?.erreur) {
                    this.erreur = resultat.erreur;
                    return;
                }
                this.montant = '';
                this.note = '';
                this.message = @js(__('caisse.transfert_reussi'));
                await this.chargerSoldes();
            } finally {
                this.enConfirmation = false;
                this.confirmationOuverte = false;
            }
        },
    }"
    x-init="chargerSoldes()"
    class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
>
    <div class="mx-auto max-w-[600px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div id="guide-cible-transfert-bandeau" class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-bouton-retour :href="route('proprietaire.dashboard')" />
                        <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white truncate">{{ __('caisse.transfert_titre') }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-selecteur-langue />
                        <button
                            type="button"
                            x-on:click="window.dispatchEvent(new CustomEvent('guide:relancer', { detail: { groupe: 'transfert' } }))"
                            class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            aria-label="{{ __('caisse.guide_revoir') }}"
                        >?</button>
                    </div>
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.alimentation_point_label') }}</div>
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach ($this->points as $point)
                        <button
                            type="button"
                            x-on:click="pointId = {{ $point->id }}; chargerSoldes()"
                            class="text-sm font-bold px-4 py-3 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]"
                            :class="pointId === {{ $point->id }} ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                        >{{ $point->nom }}</button>
                    @endforeach
                </div>

                {{-- Source --}}
                <div id="guide-cible-transfert-source" class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.transfert_source_label') }}</div>
                <div class="flex flex-wrap gap-2 mb-1.5">
                    <template x-for="operateur in operateurs" :key="'src-' + operateur.id">
                        <button
                            type="button"
                            x-on:click="sourceId = operateur.id; if (destinationId === sourceId) destinationId = operateurs.find(o => o.id !== sourceId)?.id ?? null"
                            x-text="operateur.nom"
                            class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]"
                            :class="sourceId === operateur.id ? 'border-[color:var(--color-rust)] bg-[color:var(--color-rust)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                        ></button>
                    </template>
                </div>
                <div class="text-[11px] text-[color:var(--color-ink-soft)] mb-6">
                    {{ __('caisse.transfert_solde_disponible') }} : <span class="font-[family-name:var(--font-mono)] font-bold" x-text="formaterMontant(soldes[sourceId] ?? 0)"></span>
                </div>

                {{-- Destination --}}
                <div id="guide-cible-transfert-destination" class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.transfert_destination_label') }}</div>
                <div class="flex flex-wrap gap-2 mb-6">
                    <template x-for="operateur in operateurs" :key="'dst-' + operateur.id">
                        <button
                            type="button"
                            x-on:click="if (operateur.id !== sourceId) destinationId = operateur.id"
                            x-text="operateur.nom"
                            :disabled="operateur.id === sourceId"
                            class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] disabled:opacity-30 disabled:cursor-not-allowed"
                            :class="destinationId === operateur.id ? 'border-[color:var(--color-green)] bg-[color:var(--color-green)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                        ></button>
                    </template>
                </div>
                <p class="text-[11px] text-[color:var(--color-ink-soft)] -mt-4 mb-6" x-show="sourceId === destinationId" x-cloak>{{ __('caisse.transfert_choisir_destination_differente') }}</p>

                {{-- Montant --}}
                <div id="guide-cible-transfert-montant" class="mt-2 px-1">
                    <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-semibold text-[color:var(--color-ink-soft)] tracking-wide text-center">{{ __('caisse.transfert_montant_label') }}</div>
                    <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl px-4 py-3.5 mt-1.5 max-w-[340px] mx-auto focus-within:border-[color:var(--color-ink)]">
                        <input
                            type="text"
                            inputmode="numeric"
                            x-model="montant"
                            x-on:input="montant = $event.target.value.replace(/\D/g, '').slice(0, 9)"
                            placeholder="0"
                            dir="ltr"
                            class="flex-1 min-w-0 border-none bg-transparent outline-none font-[family-name:var(--font-mono)] font-bold text-[32px] text-[color:var(--color-ink)] tabular-nums text-end placeholder:text-[color:var(--color-ink-soft)]/50"
                        >
                        <span class="text-sm font-semibold text-[color:var(--color-ink-soft)] flex-shrink-0">{{ __('caisse.devise') }}</span>
                    </div>
                </div>

                <div class="mt-4">
                    <input
                        type="text"
                        x-model="note"
                        placeholder="{{ __('caisse.alimentation_note_placeholder') }}"
                        class="w-full bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-xl px-4 py-3 text-sm font-semibold text-[color:var(--color-ink)] outline-none"
                    >
                </div>

                <button
                    type="button"
                    x-on:click="ouvrirConfirmation()"
                    :disabled="enConfirmation || montant === '' || sourceId === destinationId"
                    class="w-full max-w-[340px] mx-auto mt-4 flex rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white items-center justify-center gap-2 bg-[color:var(--color-ink)] shadow-lg disabled:opacity-60"
                >
                    {{ __('caisse.transfert_confirmer') }}
                </button>
                <p x-show="message" x-cloak x-text="message" class="text-sm text-center mt-3 font-semibold text-[color:var(--color-green-deep)]"></p>
                <p x-show="erreur" x-cloak x-text="erreur" class="text-sm text-center mt-3 font-semibold text-[color:var(--color-rust-deep)]"></p>

                @if ($this->transfertsRecents->isNotEmpty())
                    <div class="mt-8">
                        <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.transfert_historique') }}</div>
                        <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                            @foreach ($this->transfertsRecents as $t)
                                <div class="flex items-center justify-between py-2.5">
                                    <div>
                                        <div class="text-sm font-semibold text-[color:var(--color-ink)]">{{ $t->point->nom }} — {{ $t->operateurSource->nom }} → {{ $t->operateurDestination->nom }}</div>
                                        <div class="text-[11px] text-[color:var(--color-ink-soft)]">{{ $t->created_at->format('d/m/Y H:i') }} @if($t->note) · {{ $t->note }} @endif</div>
                                    </div>
                                    <div class="font-[family-name:var(--font-mono)] font-bold text-sm text-[color:var(--color-ink)]" dir="ltr">{{ number_format($t->montant, 0, ',', ' ') }}</div>
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
                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-sm text-[color:var(--color-ink-soft)]">{{ __('caisse.transfert_source_label') }}</span>
                                <span class="text-sm font-bold text-[color:var(--color-rust-deep)]" x-text="operateurs.find(o => o.id === sourceId)?.nom ?? ''"></span>
                            </div>
                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-sm text-[color:var(--color-ink-soft)]">{{ __('caisse.transfert_destination_label') }}</span>
                                <span class="text-sm font-bold text-[color:var(--color-green-deep)]" x-text="operateurs.find(o => o.id === destinationId)?.nom ?? ''"></span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm font-bold text-[color:var(--color-ink)]">{{ __('caisse.confirmation_montant') }}</span>
                                <span class="text-lg font-[family-name:var(--font-mono)] font-bold text-[color:var(--color-ink)]" dir="ltr" x-text="formaterMontant(montant)"></span>
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
        groupe="transfert"
        :visible-initial="$this->guideAAfficher"
        :etapes="[
            ['cible' => '#guide-cible-transfert-bandeau', 'texte' => __('caisse.guide_transfert_1')],
            ['cible' => '#guide-cible-transfert-source', 'texte' => __('caisse.guide_transfert_2')],
            ['cible' => '#guide-cible-transfert-destination', 'texte' => __('caisse.guide_transfert_3')],
            ['cible' => '#guide-cible-transfert-montant', 'texte' => __('caisse.guide_transfert_4')],
        ]"
        wire-termine="$wire.marquerGuideVu()"
    />
</div>

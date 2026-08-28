<div
    x-data="{
        operateurs: {{ Js::from($this->soldesTheoriquesParOperateur->map(fn ($l) => ['id' => $l['operateur']->id, 'nom' => $l['operateur']->nom, 'soldeTheorique' => $l['solde']])) }},
        index: 0,
        soldesComptes: {},
        enConfirmation: false,
        confirmationOuverte: false,
        erreur: '',
        get operateurActuel() { return this.operateurs[this.index]; },
        get soldeCompteActuel() { return this.soldesComptes[this.operateurActuel.id] ?? ''; },
        get ecartActuel() { return (parseInt(this.soldeCompteActuel || '0', 10)) - this.operateurActuel.soldeTheorique; },
        get ecartActuelImportant() {
            const seuil = Math.max(1000, this.operateurActuel.soldeTheorique * 0.2);
            return Math.abs(this.ecartActuel) >= seuil;
        },
        get ecartTotal() { return this.soldeCompteTotal - this.soldeTheoriqueTotal; },
        get tousSaisis() { return this.operateurs.every(o => this.soldesComptes[o.id] !== undefined && this.soldesComptes[o.id] !== ''); },
        get soldeTheoriqueTotal() { return this.operateurs.reduce((s, o) => s + o.soldeTheorique, 0); },
        get soldeCompteTotal() { return this.operateurs.reduce((s, o) => s + (parseInt(this.soldesComptes[o.id] || '0', 10)), 0); },
        formaterMontant(v) { return Number(v || 0).toLocaleString('fr-FR').replace(/,/g, ' '); },
        texteEcart(e) {
            const t = window.ComptoirTraductions ?? {};
            if (e === 0) return t.clotureEcartAucun ?? @js(__('caisse.cloture_ecart_aucun'));
            const montant = this.formaterMontant(Math.abs(e));
            const gabarit = e > 0
                ? (t.clotureEcartPositif ?? @js(__('caisse.cloture_ecart_positif')))
                : (t.clotureEcartNegatif ?? @js(__('caisse.cloture_ecart_negatif')));

            return gabarit.replace(':montant', montant);
        },
        suivant() { if (this.index < this.operateurs.length - 1) this.index++; },
        precedent() { if (this.index > 0) this.index--; },
        async cloturer() {
            if (! this.tousSaisis || this.enConfirmation) return;
            this.erreur = '';
            this.enConfirmation = true;
            try {
                const payload = {};
                this.operateurs.forEach(o => { payload[o.id] = parseInt(this.soldesComptes[o.id], 10); });
                const resultat = await this.$wire.cloturer(payload);
                if (resultat?.erreur) {
                    this.erreur = resultat.erreur;
                }
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
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-bouton-retour :href="route('caisse.saisie')" />
                        <div class="min-w-0">
                            <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white truncate">{{ __('caisse.cloture_titre') }}</span>
                            <b class="block text-sm font-semibold mt-0.5 text-white truncate">{{ $this->point->nom }}</b>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <x-selecteur-langue />
                    </div>
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                @if ($this->clotureDuJour)
                    @php $c = $this->clotureDuJour; @endphp
                    <div class="text-center py-6">
                        <div class="flex justify-center mb-3">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-green-deep)]"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/></svg>
                        </div>
                        <p class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-[color:var(--color-ink)] mb-1">{{ __('caisse.cloture_deja_faite') }}</p>

                        <div class="mt-6 grid grid-cols-2 gap-3 text-start">
                            <div class="bg-[color:var(--color-paper)] rounded-xl p-4 border border-[color:var(--color-line)]">
                                <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase">{{ __('caisse.cloture_solde_theorique') }}</div>
                                <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-ink)] mt-1 text-start" dir="ltr">{{ number_format($c->solde_theorique, 0, ',', ' ') }}</div>
                            </div>
                            <div class="bg-[color:var(--color-paper)] rounded-xl p-4 border border-[color:var(--color-line)]">
                                <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase">{{ __('caisse.cloture_solde_compte') }}</div>
                                <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-ink)] mt-1 text-start" dir="ltr">{{ number_format($c->solde_compte, 0, ',', ' ') }}</div>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl p-4 font-semibold text-sm
                            {{ $c->ecart === 0 ? 'bg-[color:var(--color-green)]/10 text-[color:var(--color-green-deep)]' : 'bg-[color:var(--color-rust)]/10 text-[color:var(--color-rust-deep)]' }}">
                            @if ($c->ecart === 0)
                                {{ __('caisse.cloture_ecart_aucun') }}
                            @elseif ($c->ecart > 0)
                                {{ __('caisse.cloture_ecart_positif', ['montant' => number_format($c->ecart, 0, ',', ' ')]) }}
                            @else
                                {{ __('caisse.cloture_ecart_negatif', ['montant' => number_format(abs($c->ecart), 0, ',', ' ')]) }}
                            @endif
                        </div>

                        @if ($c->details->isNotEmpty())
                            <div class="mt-6 text-start">
                                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] uppercase mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.cloture_recapitulatif') }}</div>
                                <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                                    @foreach ($c->details as $detail)
                                        <div class="flex items-center justify-between py-2.5">
                                            <span class="text-sm font-semibold text-[color:var(--color-ink)]">{{ $detail->operateur->nom }}</span>
                                            <span class="text-sm font-[family-name:var(--font-mono)] font-bold text-end {{ $detail->ecart === 0 ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-rust-deep)]' }}" dir="ltr">
                                                {{ number_format($detail->solde_compte, 0, ',', ' ') }}
                                                @if ($detail->ecart !== 0)
                                                    <span class="text-xs">({{ $detail->ecart > 0 ? '+' : '' }}{{ number_format($detail->ecart, 0, ',', ' ') }})</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('caisse.historique') }}" class="inline-block mt-6 text-sm font-semibold text-[color:var(--color-ink)] underline">
                            {{ __('caisse.cloture_voir_historique') }}
                        </a>
                    </div>
                @else
                    <div class="flex justify-end mb-2">
                        <button
                            type="button"
                            x-data
                            x-on:click="window.dispatchEvent(new CustomEvent('guide:relancer', { detail: { groupe: 'cloture' } }))"
                            class="w-6 h-6 rounded-full bg-[color:var(--color-sand-deep)] hover:bg-[color:var(--color-line)] flex items-center justify-center text-[11px] font-bold text-[color:var(--color-ink-soft)] flex-shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                            aria-label="{{ __('caisse.guide_revoir') }}"
                        >?</button>
                    </div>

                    {{-- Progression --}}
                    <div id="guide-cible-cloture-progression" class="flex items-center justify-center gap-1.5 mb-5">
                        <template x-for="(op, i) in operateurs" :key="op.id">
                            <span class="h-1.5 rounded-full transition-all" :class="i === index ? 'w-6 bg-[color:var(--color-ink)]' : (soldesComptes[op.id] !== undefined && soldesComptes[op.id] !== '' ? 'w-1.5 bg-[color:var(--color-green)]' : 'w-1.5 bg-[color:var(--color-line)]')"></span>
                        </template>
                    </div>
                    <div class="text-center text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1" x-text="@js(__('caisse.cloture_etape', ['actuel' => ':actuel', 'total' => ':total'])).replace(':actuel', index + 1).replace(':total', operateurs.length)"></div>
                    <div class="text-center mb-6">
                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-lg text-[color:var(--color-ink)]" x-text="operateurActuel.nom"></div>
                    </div>

                    <p class="text-center text-sm text-[color:var(--color-ink-soft)] max-w-[320px] mx-auto mb-6">{{ __('caisse.cloture_aide') }}</p>

                    <div id="guide-cible-cloture-theorique" class="text-center mb-6">
                        <div class="text-xs font-semibold uppercase tracking-wide text-[color:var(--color-ink-soft)]">{{ __('caisse.cloture_solde_theorique') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-3xl text-[color:var(--color-ink)] mt-1" dir="ltr" x-text="formaterMontant(operateurActuel.soldeTheorique)"></div>
                    </div>

                    <div id="guide-cible-cloture-compte" class="mt-6 px-1">
                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-semibold text-[color:var(--color-ink-soft)] tracking-wide text-center">{{ __('caisse.cloture_solde_compte') }}</div>
                        <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl px-4 py-3.5 mt-1.5 max-w-[340px] mx-auto focus-within:border-[color:var(--color-ink)]">
                            <input
                                type="text"
                                inputmode="numeric"
                                :value="soldeCompteActuel"
                                x-on:input="soldesComptes[operateurActuel.id] = $event.target.value.replace(/\D/g, '').slice(0, 9)"
                                placeholder="0"
                                dir="ltr"
                                class="flex-1 min-w-0 border-none bg-transparent outline-none font-[family-name:var(--font-mono)] font-bold text-[32px] text-[color:var(--color-ink)] tabular-nums text-end placeholder:text-[color:var(--color-ink-soft)]/50"
                            >
                            <span class="text-sm font-semibold text-[color:var(--color-ink-soft)] flex-shrink-0">{{ __('caisse.devise') }}</span>
                        </div>
                        <div
                            x-show="soldeCompteActuel !== '' && ! ecartActuelImportant"
                            x-cloak
                            class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[12.5px] font-semibold min-h-[16px] mt-1.5"
                            :class="ecartActuel === 0 ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]'"
                            x-text="texteEcart(ecartActuel)"
                        ></div>
                    </div>

                    <div
                        x-show="soldeCompteActuel !== '' && ecartActuelImportant"
                        x-cloak
                        class="flex items-start gap-2.5 mt-4 max-w-[340px] mx-auto bg-[color:var(--color-rust)]/10 border-[1.5px] border-[color:var(--color-rust)]/30 rounded-xl px-3.5 py-3"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-rust-deep)] flex-shrink-0"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                        <div>
                            <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-sm text-[color:var(--color-rust-deep)]" x-text="texteEcart(ecartActuel)"></div>
                            <div class="text-xs text-[color:var(--color-rust-deep)] mt-0.5">{{ __('caisse.cloture_ecart_important') }}</div>
                        </div>
                    </div>

                    {{-- Navigation entre opérateurs / clôture --}}
                    <div id="guide-cible-cloture-navigation" class="flex gap-2.5 mt-4 max-w-[340px] mx-auto">
                        <button
                            type="button"
                            x-show="index > 0"
                            x-on:click="precedent()"
                            class="flex-1 rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-[color:var(--color-ink)] border-[1.5px] border-[color:var(--color-line)] hover:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                        >{{ __('caisse.cloture_operateur_precedent') }}</button>

                        <button
                            type="button"
                            x-show="index < operateurs.length - 1"
                            x-on:click="suivant()"
                            :disabled="soldeCompteActuel === ''"
                            class="flex-1 rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white bg-[color:var(--color-ink)] shadow-lg disabled:opacity-40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                        >{{ __('caisse.cloture_operateur_suivant') }}</button>

                        <button
                            type="button"
                            x-show="index === operateurs.length - 1"
                            x-on:click="confirmationOuverte = true"
                            :disabled="! tousSaisis"
                            class="flex-1 rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white bg-[color:var(--color-ink)] shadow-lg disabled:opacity-40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                        >{{ __('caisse.cloture_confirmer') }}</button>
                    </div>
                    <p x-show="erreur" x-cloak x-text="erreur" role="alert" class="text-xs text-[color:var(--color-rust-deep)] mt-1.5 text-center"></p>

                    {{-- Modale de confirmation --}}
                    <template x-teleport="body">
                        <div
                            x-show="confirmationOuverte"
                            x-cloak
                            x-on:keydown.escape.window="confirmationOuverte = false"
                            class="fixed inset-0 z-50 flex items-center justify-center px-4"
                        >
                            <div class="absolute inset-0 bg-[color:var(--color-ink)]/60 backdrop-blur-sm" x-on:click="confirmationOuverte = false"></div>

                            <div
                                x-show="confirmationOuverte"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                role="dialog"
                                aria-modal="true"
                                class="relative bg-[color:var(--color-paper)] rounded-2xl shadow-xl w-full max-w-sm overflow-hidden {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
                            >
                                <div class="p-6">
                                    <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-[color:var(--color-ink)] mb-1.5">
                                        {{ __('caisse.cloture_confirmer_titre') }}
                                    </div>
                                    <p class="text-sm text-[color:var(--color-ink-soft)] mb-4">
                                        {{ __('caisse.cloture_confirmer_texte') }}
                                    </p>

                                    <div class="divide-y divide-dashed divide-[color:var(--color-line)] border-y border-dashed border-[color:var(--color-line)]">
                                        <template x-for="op in operateurs" :key="op.id">
                                            <div class="flex items-center justify-between py-2">
                                                <span class="text-sm font-semibold text-[color:var(--color-ink)]" x-text="op.nom"></span>
                                                <span class="text-sm font-[family-name:var(--font-mono)] font-bold text-[color:var(--color-ink)]" dir="ltr" x-text="formaterMontant(soldesComptes[op.id])"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex items-center justify-between pt-3">
                                        <span class="text-sm font-bold text-[color:var(--color-ink)]">{{ __('caisse.cloture_solde_compte') }}</span>
                                        <span class="text-base font-[family-name:var(--font-mono)] font-bold text-[color:var(--color-ink)]" dir="ltr" x-text="formaterMontant(soldeCompteTotal)"></span>
                                    </div>
                                    <div class="flex items-center justify-between pt-1.5" x-show="ecartTotal !== 0">
                                        <span class="text-xs font-semibold text-[color:var(--color-rust-deep)]">{{ __('caisse.cloture_ecart_total') }}</span>
                                        <span class="text-sm font-[family-name:var(--font-mono)] font-bold text-[color:var(--color-rust-deep)]" dir="ltr" x-text="(ecartTotal > 0 ? '+' : '') + formaterMontant(ecartTotal)"></span>
                                    </div>
                                </div>

                                <div class="flex border-t border-[color:var(--color-line)]">
                                    <button
                                        type="button"
                                        x-on:click="confirmationOuverte = false"
                                        class="flex-1 text-sm font-semibold py-3.5 text-[color:var(--color-ink-soft)] hover:bg-[color:var(--color-sand-deep)]"
                                    >{{ __('caisse.annuler') }}</button>
                                    <button
                                        type="button"
                                        x-on:click="cloturer()"
                                        :disabled="enConfirmation"
                                        class="flex-1 text-sm font-semibold py-3.5 text-white bg-[color:var(--color-ink)] hover:opacity-90 border-s border-[color:var(--color-line)] disabled:opacity-60"
                                    ><span x-show="! enConfirmation">{{ __('caisse.cloture_confirmer') }}</span><span x-show="enConfirmation" x-cloak>…</span></button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <x-guide-decouverte
                        groupe="cloture"
                        :visible-initial="$this->guideAAfficher"
                        :etapes="[
                            ['cible' => '#guide-cible-cloture-progression', 'texte' => __('caisse.guide_cloture_1')],
                            ['cible' => '#guide-cible-cloture-theorique', 'texte' => __('caisse.guide_cloture_2')],
                            ['cible' => '#guide-cible-cloture-compte', 'texte' => __('caisse.guide_cloture_3')],
                            ['cible' => '#guide-cible-cloture-navigation', 'texte' => __('caisse.guide_cloture_4')],
                        ]"
                        wire-termine="$wire.marquerGuideVu()"
                    />
                @endif
            </div>
        </div>
    </div>
</div>

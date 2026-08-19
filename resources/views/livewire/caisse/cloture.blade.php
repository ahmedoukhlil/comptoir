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
        get tousSaisis() { return this.operateurs.every(o => this.soldesComptes[o.id] !== undefined && this.soldesComptes[o.id] !== ''); },
        get soldeTheoriqueTotal() { return this.operateurs.reduce((s, o) => s + o.soldeTheorique, 0); },
        get soldeCompteTotal() { return this.operateurs.reduce((s, o) => s + (parseInt(this.soldesComptes[o.id] || '0', 10)), 0); },
        formaterMontant(v) { return Number(v || 0).toLocaleString('fr-FR').replace(/,/g, ' '); },
        texteEcart(e) {
            const t = window.ComptoirTraductions ?? {};
            if (e === 0) return t.clotureEcartAucun ?? '';
            const montant = this.formaterMontant(Math.abs(e));
            const gabarit = e > 0 ? t.clotureEcartPositif : t.clotureEcartNegatif;

            return (gabarit ?? '').replace(':montant', montant);
        },
        taper(c) {
            const actuel = this.soldeCompteActuel;
            if (String(actuel).length >= 9) return;
            this.soldesComptes[this.operateurActuel.id] = String(parseInt((actuel || '0') + c, 10));
        },
        effacer() { this.soldesComptes[this.operateurActuel.id] = ''; },
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
                <div class="flex items-start justify-between">
                    <div>
                        <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white">{{ __('caisse.cloture_titre') }}</span>
                        <b class="block text-sm font-semibold mt-0.5 text-[#B9C6E6]">{{ $this->point->nom }}</b>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-selecteur-langue />
                    </div>
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                @if ($this->clotureDuJour)
                    @php $c = $this->clotureDuJour; @endphp
                    <div class="text-center py-6">
                        <div class="text-4xl mb-3">✓</div>
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
                    {{-- Progression --}}
                    <div class="flex items-center justify-center gap-1.5 mb-5">
                        <template x-for="(op, i) in operateurs" :key="op.id">
                            <span class="h-1.5 rounded-full transition-all" :class="i === index ? 'w-6 bg-[color:var(--color-ink)]' : (soldesComptes[op.id] !== undefined && soldesComptes[op.id] !== '' ? 'w-1.5 bg-[color:var(--color-green)]' : 'w-1.5 bg-[color:var(--color-line)]')"></span>
                        </template>
                    </div>
                    <div class="text-center text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1" x-text="@js(__('caisse.cloture_etape', ['actuel' => ':actuel', 'total' => ':total'])).replace(':actuel', index + 1).replace(':total', operateurs.length)"></div>
                    <div class="text-center mb-6">
                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-lg text-[color:var(--color-ink)]" x-text="operateurActuel.nom"></div>
                    </div>

                    <div class="text-center mb-6">
                        <div class="text-xs font-semibold uppercase tracking-wide text-[color:var(--color-ink-soft)]">{{ __('caisse.cloture_solde_theorique') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-3xl text-[color:var(--color-ink)] mt-1" dir="ltr" x-text="formaterMontant(operateurActuel.soldeTheorique)"></div>
                    </div>

                    <div class="text-center mt-6 px-1">
                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-semibold text-[color:var(--color-ink-soft)] tracking-wide">{{ __('caisse.cloture_solde_compte') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-[48px] text-[color:var(--color-ink)] tabular-nums leading-none mt-1.5" dir="ltr" x-text="soldeCompteActuel !== '' ? formaterMontant(soldeCompteActuel) : '0'"></div>
                        <div
                            x-show="soldeCompteActuel !== ''"
                            x-cloak
                            class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[12.5px] font-semibold min-h-[16px] mt-1.5"
                            :class="ecartActuel === 0 ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]'"
                            x-text="texteEcart(ecartActuel)"
                        ></div>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5 mt-4 max-w-[340px] mx-auto">
                        <template x-for="chiffre in ['1','2','3','4','5','6','7','8','9']" :key="chiffre">
                            <button
                                type="button"
                                x-on:click="taper(chiffre)"
                                x-text="chiffre"
                                class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-mono)] text-xl font-bold text-[color:var(--color-ink)] active:scale-95 active:bg-[color:var(--color-sand-deep)] hover:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                            ></button>
                        </template>
                        <button type="button" x-on:click="effacer()" class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-bold text-[color:var(--color-ink-soft)] hover:border-[color:var(--color-ink)] hover:text-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition">{{ __('caisse.effacer') }}</button>
                        <button type="button" x-on:click="taper('0')" class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-mono)] text-xl font-bold text-[color:var(--color-ink)] active:scale-95 active:bg-[color:var(--color-sand-deep)] hover:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition">0</button>
                        <button type="button" x-on:click="taper('000')" class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-bold text-[color:var(--color-ink-soft)] hover:border-[color:var(--color-ink)] hover:text-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition">000</button>
                    </div>

                    {{-- Navigation entre opérateurs / clôture --}}
                    <div class="flex gap-2.5 mt-4 max-w-[340px] mx-auto">
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
                    <p x-show="erreur" x-cloak x-text="erreur" class="text-xs text-[color:var(--color-rust-deep)] mt-1.5 text-center"></p>

                    <div class="text-center mt-8">
                        <a href="{{ route('caisse.saisie') }}" class="text-sm font-semibold text-[color:var(--color-ink-soft)] underline">
                            {{ __('caisse.historique_retour') }}
                        </a>
                    </div>

                    {{-- Modale de confirmation --}}
                    <template x-teleport="body">
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
                @endif
            </div>
        </div>
    </div>
</div>

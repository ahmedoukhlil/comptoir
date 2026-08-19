<div
    x-data="{
        soldeCompte: '',
        enConfirmation: false,
        erreur: '',
        soldeTheorique: {{ Js::from($this->soldeTheorique) }},
        get ecart() { return (parseInt(this.soldeCompte || '0', 10)) - this.soldeTheorique; },
        formaterMontant(v) { return Number(v || 0).toLocaleString('fr-FR').replace(/,/g, ' '); },
        texteEcart(e) {
            const t = window.ComptoirTraductions ?? {};
            if (e === 0) return t.clotureEcartAucun ?? '';
            const montant = this.formaterMontant(Math.abs(e));
            const gabarit = e > 0 ? t.clotureEcartPositif : t.clotureEcartNegatif;

            return (gabarit ?? '').replace(':montant', montant);
        },
        taper(c) {
            if (this.soldeCompte.length >= 9) return;
            this.soldeCompte = String(parseInt((this.soldeCompte || '0') + c, 10));
        },
        effacer() { this.soldeCompte = ''; },
        async cloturer() {
            if (this.soldeCompte === '' || this.enConfirmation) return;
            if (! confirm(@js(__('caisse.cloture_confirmer')) + ' ?')) return;
            this.erreur = '';
            this.enConfirmation = true;
            try {
                const resultat = await this.$wire.cloturer(parseInt(this.soldeCompte, 10));
                if (resultat?.erreur) {
                    this.erreur = resultat.erreur;
                }
            } finally {
                this.enConfirmation = false;
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
                                <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-ink)] mt-1">{{ number_format($c->solde_theorique, 0, ',', ' ') }}</div>
                            </div>
                            <div class="bg-[color:var(--color-paper)] rounded-xl p-4 border border-[color:var(--color-line)]">
                                <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase">{{ __('caisse.cloture_solde_compte') }}</div>
                                <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-ink)] mt-1">{{ number_format($c->solde_compte, 0, ',', ' ') }}</div>
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
                        <a href="{{ route('caisse.historique') }}" class="inline-block mt-6 text-sm font-semibold text-[color:var(--color-ink)] underline">
                            {{ __('caisse.cloture_voir_historique') }}
                        </a>
                    </div>
                @else
                    <div class="text-center mb-6">
                        <div class="text-xs font-semibold uppercase tracking-wide text-[color:var(--color-ink-soft)]">{{ __('caisse.cloture_solde_theorique') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-3xl text-[color:var(--color-ink)] mt-1" x-text="formaterMontant(soldeTheorique)"></div>
                    </div>

                    <div class="text-center mt-6 px-1">
                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-semibold text-[color:var(--color-ink-soft)] tracking-wide">{{ __('caisse.cloture_solde_compte') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-[48px] text-[color:var(--color-ink)] tabular-nums leading-none mt-1.5" x-text="soldeCompte !== '' ? formaterMontant(soldeCompte) : '0'"></div>
                        <div
                            x-show="soldeCompte !== ''"
                            x-cloak
                            class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[12.5px] font-semibold min-h-[16px] mt-1.5"
                            :class="ecart === 0 ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]'"
                            x-text="texteEcart(ecart)"
                        ></div>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5 mt-4 max-w-[340px] mx-auto">
                        <template x-for="chiffre in ['1','2','3','4','5','6','7','8','9']" :key="chiffre">
                            <button
                                type="button"
                                x-on:click="taper(chiffre)"
                                x-text="chiffre"
                                class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-mono)] text-xl font-bold text-[color:var(--color-ink)] active:scale-95 active:bg-[color:var(--color-sand-deep)] transition"
                            ></button>
                        </template>
                        <button type="button" x-on:click="effacer()" class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-bold text-[color:var(--color-ink-soft)]">{{ __('caisse.effacer') }}</button>
                        <button type="button" x-on:click="taper('0')" class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-mono)] text-xl font-bold text-[color:var(--color-ink)] active:scale-95 active:bg-[color:var(--color-sand-deep)] transition">0</button>
                        <button type="button" x-on:click="taper('000')" class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-bold text-[color:var(--color-ink-soft)]">000</button>
                    </div>

                    <button
                        type="button"
                        x-on:click="cloturer()"
                        :disabled="enConfirmation"
                        class="w-full max-w-[340px] mx-auto mt-4 flex rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white items-center justify-center gap-2 bg-[color:var(--color-ink)] shadow-lg disabled:opacity-60"
                    >
                        {{ __('caisse.cloture_confirmer') }}
                    </button>
                    <p x-show="erreur" x-cloak x-text="erreur" class="text-xs text-[color:var(--color-rust-deep)] mt-1.5 text-center"></p>

                    <div class="text-center mt-8">
                        <a href="{{ route('caisse.saisie') }}" class="text-sm font-semibold text-[color:var(--color-ink-soft)] underline">
                            {{ __('caisse.historique_retour') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

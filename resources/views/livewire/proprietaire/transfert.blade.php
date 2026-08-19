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
        formaterMontant(v) { return Number(v || 0).toLocaleString('fr-FR').replace(/,/g, ' '); },
        async chargerSoldes() {
            this.soldes = await this.$wire.soldesDuPoint(this.pointId);
        },
        taper(c) {
            if (this.montant.length >= 9) return;
            this.montant = String(parseInt((this.montant || '0') + c, 10));
        },
        effacer() { this.montant = ''; },
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
            }
        },
    }"
    x-init="chargerSoldes()"
    class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
>
    <div class="mx-auto max-w-[600px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-start justify-between">
                    <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white">{{ __('caisse.transfert_titre') }}</span>
                    <x-selecteur-langue />
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
                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.transfert_source_label') }}</div>
                <div class="flex flex-wrap gap-2 mb-1.5">
                    <template x-for="operateur in operateurs" :key="'src-' + operateur.id">
                        <button
                            type="button"
                            x-on:click="sourceId = operateur.id"
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
                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.transfert_destination_label') }}</div>
                <div class="flex flex-wrap gap-2 mb-6">
                    <template x-for="operateur in operateurs" :key="'dst-' + operateur.id">
                        <button
                            type="button"
                            x-on:click="destinationId = operateur.id"
                            x-text="operateur.nom"
                            class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]"
                            :class="destinationId === operateur.id ? 'border-[color:var(--color-green)] bg-[color:var(--color-green)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                        ></button>
                    </template>
                </div>

                {{-- Montant --}}
                <div class="text-center mb-2 px-1">
                    <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-semibold text-[color:var(--color-ink-soft)] tracking-wide">{{ __('caisse.transfert_montant_label') }}</div>
                    <div class="font-[family-name:var(--font-mono)] font-bold text-[48px] text-[color:var(--color-ink)] tabular-nums leading-none mt-1.5" x-text="montant !== '' ? formaterMontant(montant) : '0'"></div>
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
                    x-on:click="confirmer()"
                    :disabled="enConfirmation"
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

                <div class="text-center mt-8">
                    <a href="{{ route('proprietaire.dashboard') }}" class="text-sm font-semibold text-[color:var(--color-ink-soft)] underline">
                        {{ __('caisse.retour_dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

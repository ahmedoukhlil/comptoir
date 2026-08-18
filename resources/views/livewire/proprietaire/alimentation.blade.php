<div
    x-data="{
        pointId: {{ Js::from($this->points->first()?->id) }},
        operateurs: {{ Js::from($this->operateursPourJs) }},
        montants: {{ Js::from($this->operateurs->mapWithKeys(fn ($o) => [$o->id => ''])) }},
        note: '',
        message: '',
        erreur: '',
        enConfirmation: false,
        get total() {
            return Object.values(this.montants).reduce((s, v) => s + (parseInt(v || '0', 10)), 0);
        },
        formaterMontant(v) { return Number(v || 0).toLocaleString('fr-FR').replace(/,/g, ' '); },
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
            }
        },
    }"
    class="min-h-screen bg-gradient-to-b from-[#0A2242] to-[color:var(--color-ink)] md:bg-none md:bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
>
    <div class="mx-auto max-w-[600px] md:py-10 md:px-6">
        <div class="bg-gradient-to-br from-[color:var(--color-sand)] via-[#F1F6FC] to-[color:var(--color-sand-deep)] md:rounded-[22px] md:border md:border-[color:var(--color-line)] md:shadow-2xl overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-[color:var(--color-sand)] px-5 pt-8 pb-6 md:px-9 md:py-6">
                <div class="flex items-start justify-between">
                    <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base">{{ __('caisse.alimentation_titre') }}</span>
                    <x-selecteur-langue />
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.alimentation_point_label') }}</div>
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

                <div class="flex flex-col gap-2.5">
                    @foreach ($this->operateurs as $operateur)
                        <div class="flex items-center gap-3 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-xl px-4 py-3">
                            <div class="w-24 flex-shrink-0 text-sm font-bold text-[color:var(--color-ink)] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">
                                {{ $operateur->est_cash ? '💵' : '📱' }} {{ $operateur->nom }}
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
                    type="button"
                    x-on:click="confirmer()"
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
                                    <div class="font-[family-name:var(--font-mono)] font-bold text-sm text-[color:var(--color-ink)]">+ {{ number_format($alim->montant, 0, ',', ' ') }}</div>
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

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
                        <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.operateur_nom_label') }}</label>
                        <input type="text" wire:model="nom" placeholder="{{ __('caisse.operateur_nom_placeholder') }}" class="w-full rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                        @error('nom') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1.5">{{ __('caisse.operateur_type_label') }}</label>
                        <div class="flex gap-1.5">
                            <button
                                type="button"
                                wire:click="$set('estCash', false)"
                                class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] {{ ! $estCash ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                            >📱 {{ __('caisse.operateur_type_mobile') }}</button>
                            <button
                                type="button"
                                wire:click="$set('estCash', true)"
                                class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] {{ $estCash ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                            >💵 {{ __('caisse.operateur_type_cash') }}</button>
                        </div>
                    </div>

                    @unless ($estCash)
                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.operateur_commission_label') }}</label>
                            <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-lg px-3.5 py-2.5">
                                <input type="number" step="0.1" min="0" max="100" inputmode="decimal" dir="ltr" wire:model="commissionPourcentage" placeholder="1" class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)] font-[family-name:var(--font-mono)]">
                                <span class="text-sm text-[color:var(--color-ink-soft)]">%</span>
                            </div>
                            @error('commissionPourcentage') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endunless

                    @error('lectureSeule') <p class="text-xs text-[color:var(--color-rust-deep)]">{{ $message }}</p> @enderror

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-[color:var(--color-ink)] text-white font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold py-3 mt-2"
                    >{{ __('caisse.creer') }}</button>
                </form>

                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mt-8 mb-3 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.operateurs_liste_titre') }}</div>
                <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                    @forelse ($this->operateurs as $operateur)
                        <div class="flex items-center justify-between py-2.5 {{ ! $operateur->actif ? 'opacity-50' : '' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">{{ $operateur->est_cash ? '💵' : '📱' }}</span>
                                <div>
                                    <div class="text-sm font-semibold text-[color:var(--color-ink)]">{{ $operateur->nom }}</div>
                                    @unless ($operateur->est_cash)
                                        <div class="text-[11px] text-[color:var(--color-ink-soft)]" dir="ltr">{{ $operateur->bareme_commission['tranches'][0]['pourcentage'] ?? 0 }}%</div>
                                    @endunless
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="basculerActif({{ $operateur->id }})"
                                class="text-[11px] font-semibold px-2.5 py-1.5 min-h-[24px] rounded-md {{ $operateur->actif ? 'bg-[color:var(--color-rust)]/10 text-[color:var(--color-rust-deep)]' : 'bg-[color:var(--color-green)]/10 text-[color:var(--color-green-deep)]' }}"
                            >{{ $operateur->actif ? __('caisse.operateur_desactiver') : __('caisse.operateur_activer') }}</button>
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
</div>

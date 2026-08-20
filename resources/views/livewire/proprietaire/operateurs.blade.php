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
                @error('lectureSeule') <p class="text-xs text-[color:var(--color-rust-deep)] mb-3">{{ $message }}</p> @enderror

                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-3 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.operateurs_liste_titre') }}</div>
                <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                    @forelse ($this->operateurs as $operateur)
                        @php($actifPourTenant = (bool) $operateur->tenants->first()?->pivot->actif)
                        <div class="flex items-center justify-between py-2.5 {{ ! $actifPourTenant ? 'opacity-50' : '' }}">
                            <div class="flex items-center gap-2.5">
                                <x-icone-type-operateur :est-cash="$operateur->est_cash" width="17" height="17" class="text-[color:var(--color-ink-soft)] flex-shrink-0" />
                                <div class="text-sm font-semibold text-[color:var(--color-ink)]">{{ $operateur->nom }}</div>
                            </div>
                            <button
                                type="button"
                                wire:click="basculerActif({{ $operateur->id }})"
                                aria-label="{{ ($actifPourTenant ? __('caisse.operateur_desactiver') : __('caisse.operateur_activer')) . ' ' . $operateur->nom }}"
                                class="text-[11px] font-semibold px-2.5 py-1.5 min-h-[24px] rounded-md flex-shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 {{ $actifPourTenant ? 'bg-[color:var(--color-rust)]/10 text-[color:var(--color-rust-deep)] focus-visible:outline-[color:var(--color-rust-deep)]' : 'bg-[color:var(--color-green)]/10 text-[color:var(--color-green-deep)] focus-visible:outline-[color:var(--color-green-deep)]' }}"
                            >{{ $actifPourTenant ? __('caisse.operateur_desactiver') : __('caisse.operateur_activer') }}</button>
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

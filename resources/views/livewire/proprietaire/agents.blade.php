<div class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <div class="mx-auto max-w-[600px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-start justify-between">
                    <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white">{{ __('caisse.agents_titre') }}</span>
                    <x-selecteur-langue />
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-3 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.agents_nouvel_agent') }}</div>

                @if ($motDePasseGenere)
                    <div class="bg-[color:var(--color-green)]/10 border border-[color:var(--color-green)]/30 rounded-xl p-4 mb-6">
                        <div class="text-sm font-semibold text-[color:var(--color-green-deep)]">{{ __('caisse.agent_cree') }}</div>
                        <div class="text-xs text-[color:var(--color-ink-soft)] mt-2">{{ __('caisse.agent_mot_de_passe_genere') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-ink)] mt-0.5" dir="ltr">{{ $motDePasseGenere }}</div>
                        <p class="text-xs text-[color:var(--color-ink-soft)] mt-2">{{ __('caisse.agent_mot_de_passe_note') }}</p>
                        <button type="button" wire:click="$set('motDePasseGenere', null)" class="text-xs font-semibold text-[color:var(--color-ink)] underline mt-3">
                            {{ __('caisse.agent_ajouter_un_autre') }}
                        </button>
                    </div>
                @else
                    <form wire:submit="creer" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1.5">{{ __('caisse.alimentation_point_label') }}</label>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($this->points as $point)
                                    <button
                                        type="button"
                                        wire:click="$set('pointId', {{ $point->id }})"
                                        class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] {{ $pointId === $point->id ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                                    >{{ $point->nom }}</button>
                                @endforeach
                            </div>
                            @error('pointId') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1.5">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.agent_nom_label') }}</label>
                            <input type="text" wire:model="nom" class="w-full rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                            @error('nom') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.agent_telephone_label') }}</label>
                            <input type="tel" inputmode="numeric" dir="ltr" wire:model="telephone" class="w-full rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-3.5 py-2.5 text-sm font-[family-name:var(--font-mono)] outline-none focus:border-[color:var(--color-ink)]">
                            @error('telephone') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1.5">{{ __('caisse.agent_role_label') }}</label>
                            <div class="flex gap-1.5">
                                @foreach (['agent', 'proprietaire'] as $r)
                                    <button
                                        type="button"
                                        wire:click="$set('role', '{{ $r }}')"
                                        class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] {{ $role === $r ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}"
                                    >{{ __('caisse.role_'.$r) }}</button>
                                @endforeach
                            </div>
                        </div>

                        @error('lectureSeule') <p class="text-xs text-[color:var(--color-rust-deep)]">{{ $message }}</p> @enderror

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full rounded-xl bg-[color:var(--color-ink)] text-white font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold py-3 mt-2"
                        >{{ __('caisse.creer') }}</button>
                    </form>
                @endif

                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mt-8 mb-3 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.agents_liste_titre') }}</div>
                <div class="divide-y divide-dashed divide-[color:var(--color-line)]">
                    @forelse ($this->agents as $agent)
                        <div class="flex items-center justify-between py-2.5">
                            <div>
                                <div class="text-sm font-semibold text-[color:var(--color-ink)]">{{ $agent->name }}</div>
                                <div class="text-[11px] text-[color:var(--color-ink-soft)]" dir="ltr">{{ $agent->telephone }} · {{ $agent->point?->nom }}</div>
                            </div>
                            <span class="text-[11px] font-semibold px-2 py-1 rounded-md bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink)]">
                                {{ __('caisse.role_'.$agent->role) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-[color:var(--color-ink-soft)] py-4 text-center">{{ __('caisse.agents_aucun') }}</p>
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

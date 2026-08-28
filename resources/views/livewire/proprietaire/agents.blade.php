<div class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <div class="mx-auto max-w-[600px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            <div class="px-5 pt-6 pb-4 md:px-9 md:py-6" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-start justify-between">
                    <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white">{{ __('caisse.agents_titre') }}</span>
                    <div class="flex items-center gap-2">
                        <x-selecteur-langue />
                        <button
                            type="button"
                            x-data
                            x-on:click="window.dispatchEvent(new CustomEvent('guide:relancer', { detail: { groupe: 'agents' } }))"
                            class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            aria-label="{{ __('caisse.guide_revoir') }}"
                        >?</button>
                    </div>
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-3 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.agents_nouvel_agent') }}</div>

                @if ($motDePasseGenere)
                    <div
                        x-data="{ copie: false, copier() { navigator.clipboard.writeText(@js($motDePasseGenere)).then(() => { this.copie = true; setTimeout(() => this.copie = false, 2000); }); } }"
                        class="bg-[color:var(--color-green)]/10 border border-[color:var(--color-green)]/30 rounded-xl p-4 mb-6"
                    >
                        <div class="text-sm font-semibold text-[color:var(--color-green-deep)]">{{ __('caisse.agent_cree') }}</div>
                        <div class="text-xs text-[color:var(--color-ink-soft)] mt-2">{{ __('caisse.agent_mot_de_passe_genere') }}</div>
                        <div class="flex items-center gap-2.5 mt-1.5">
                            <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-ink)]" dir="ltr">{{ $motDePasseGenere }}</div>
                            <button
                                type="button"
                                x-on:click="copier()"
                                class="flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg border-[1.5px] transition"
                                :class="copie ? 'border-[color:var(--color-green)] bg-[color:var(--color-green)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink)]'"
                            >
                                <svg x-show="! copie" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                <span x-text="copie ? @js(__('caisse.copie')) : @js(__('caisse.copier'))"></span>
                            </button>
                        </div>
                        <p class="text-xs text-[color:var(--color-ink-soft)] mt-2">{{ __('caisse.agent_mot_de_passe_note') }}</p>
                        <button type="button" wire:click="$set('motDePasseGenere', null)" class="text-xs font-semibold text-[color:var(--color-ink)] underline mt-3">
                            {{ __('caisse.agent_ajouter_un_autre') }}
                        </button>
                    </div>
                @else
                    <form wire:submit="creer" class="space-y-4">
                        <div id="guide-cible-agents-point">
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

                        <div id="guide-cible-agents-infos">
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.agent_nom_label') }}</label>
                            <input type="text" wire:model="nom" class="w-full rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                            @error('nom') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.agent_telephone_label') }}</label>
                            <input type="tel" inputmode="numeric" dir="ltr" wire:model="telephone" class="w-full rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-3.5 py-2.5 text-sm font-[family-name:var(--font-mono)] outline-none focus:border-[color:var(--color-ink)]">
                            @error('telephone') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div id="guide-cible-agents-role">
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

                <div id="guide-cible-agents-liste" class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mt-8 mb-3 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.agents_liste_titre') }}</div>
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

    <x-guide-decouverte
        groupe="agents"
        :visible-initial="$this->guideAAfficher && ! $motDePasseGenere"
        :etapes="[
            ['cible' => '#guide-cible-agents-point', 'texte' => __('caisse.guide_agents_1')],
            ['cible' => '#guide-cible-agents-infos', 'texte' => __('caisse.guide_agents_2')],
            ['cible' => '#guide-cible-agents-role', 'texte' => __('caisse.guide_agents_3')],
            ['cible' => '#guide-cible-agents-liste', 'texte' => __('caisse.guide_agents_4')],
        ]"
        wire-termine="$wire.marquerGuideVu()"
    />
</div>

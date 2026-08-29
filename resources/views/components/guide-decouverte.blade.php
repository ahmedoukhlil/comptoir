@props(['etapes', 'wireTermine', 'groupe' => 'defaut', 'visibleInitial' => true])

<div
    x-data="guideDecouverte({
        etapes: {{ Js::from($etapes) }},
        groupe: {{ Js::from($groupe) }},
        visibleInitial: {{ Js::from($visibleInitial) }},
        onTermine() { {{ $wireTermine }} },
    })"
    x-show="visible"
    x-cloak
>
    {{-- Voile semi-transparent : ne bloque pas les clics, sert juste à
         estomper le reste de l'écran pour focaliser l'attention. --}}
    <div class="fixed inset-0 z-50 bg-[color:var(--color-ink)]/30 pointer-events-none" x-show="visible"></div>

    <div
        class="fixed z-[52] w-[280px] bg-[color:var(--color-paper)] rounded-2xl shadow-2xl p-4"
        x-show="visible && positionCalculee"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        :style="`top: ${position.placement === 'bas' ? position.top : position.top - 156}px; left: ${position.left}px; max-width: calc(100vw - 24px);`"
        role="dialog"
        aria-modal="false"
        :aria-label="etapeActuelle?.texte"
    >
        <p class="text-sm text-[color:var(--color-ink)] font-medium leading-snug" x-text="etapeActuelle?.texte"></p>

        <div class="flex items-center justify-between mt-3.5">
            <div class="flex gap-1" aria-hidden="true">
                <template x-for="(e, i) in etapes" :key="i">
                    <span class="h-1.5 rounded-full transition-all" :class="i === index ? 'w-4 bg-[color:var(--color-ink)]' : 'w-1.5 bg-[color:var(--color-line)]'"></span>
                </template>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" x-on:click="fermer()" class="min-h-11 px-2 text-sm font-semibold text-[color:var(--color-ink-soft)] rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">{{ __('caisse.guide_passer') }}</button>
                <button type="button" x-on:click="suivant()" class="min-h-11 text-sm font-bold text-white bg-[color:var(--color-ink)] rounded-lg px-4 py-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                    <span x-text="derniereEtape ? @js(__('caisse.guide_terminer')) : @js(__('caisse.guide_suivant'))"></span>
                </button>
            </div>
        </div>
    </div>
</div>

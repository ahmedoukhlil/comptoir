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
        x-show="visible"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        :style="`top: ${position.placement === 'bas' ? position.top : position.top - 132}px; left: ${Math.min(Math.max(position.left - Math.min(140, window.innerWidth / 2 - 12), 12), window.innerWidth - Math.min(280, window.innerWidth - 24) - 12)}px; max-width: calc(100vw - 24px);`"
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
                <button type="button" x-on:click="fermer()" class="text-xs font-semibold text-[color:var(--color-ink-soft)]">{{ __('caisse.guide_passer') }}</button>
                <button type="button" x-on:click="suivant()" class="text-xs font-bold text-white bg-[color:var(--color-ink)] rounded-lg px-3 py-1.5">
                    <span x-text="derniereEtape ? @js(__('caisse.guide_terminer')) : @js(__('caisse.guide_suivant'))"></span>
                </button>
            </div>
        </div>
    </div>
</div>

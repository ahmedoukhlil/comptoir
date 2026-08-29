@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex bg-white/10 rounded-full p-0.5 '.$class]) }} role="group" aria-label="Langue / اللغة">
    <button
        type="button"
        wire:click="changerLangue('fr')"
        aria-pressed="{{ app()->getLocale() === 'fr' ? 'true' : 'false' }}"
        class="min-w-11 min-h-11 border-none bg-transparent font-[family-name:var(--font-heading)] text-sm font-bold px-2.5 py-2 rounded-full transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white
            {{ app()->getLocale() === 'fr' ? 'bg-[color:var(--color-sand)] text-[color:var(--color-ink)]' : 'text-white' }}"
    >
        FR
    </button>
    <button
        type="button"
        wire:click="changerLangue('ar')"
        aria-pressed="{{ app()->getLocale() === 'ar' ? 'true' : 'false' }}"
        class="min-w-11 min-h-11 border-none bg-transparent font-[family-name:var(--font-arabic)] text-sm font-bold px-2.5 py-2 rounded-full transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white
            {{ app()->getLocale() === 'ar' ? 'bg-[color:var(--color-sand)] text-[color:var(--color-ink)]' : 'text-white' }}"
    >
        عربي
    </button>
</div>

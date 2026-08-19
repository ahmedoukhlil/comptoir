@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex bg-[color:var(--color-sand-deep)] rounded-full p-0.5 '.$class]) }}>
    <button
        type="button"
        wire:click="changerLangue('fr')"
        class="border-none bg-transparent font-[family-name:var(--font-heading)] text-[11px] font-bold px-2.5 py-1.5 rounded-full transition
            {{ app()->getLocale() === 'fr' ? 'bg-[color:var(--color-card)] text-[color:var(--color-ink)] shadow-sm' : 'text-[color:var(--color-ink-soft)]' }}"
    >
        FR
    </button>
    <button
        type="button"
        wire:click="changerLangue('ar')"
        class="border-none bg-transparent font-[family-name:var(--font-arabic)] text-[11px] font-bold px-2.5 py-1.5 rounded-full transition
            {{ app()->getLocale() === 'ar' ? 'bg-[color:var(--color-card)] text-[color:var(--color-ink)] shadow-sm' : 'text-[color:var(--color-ink-soft)]' }}"
    >
        عربي
    </button>
</div>

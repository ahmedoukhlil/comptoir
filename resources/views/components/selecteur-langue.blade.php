@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex bg-white/10 rounded-full p-0.5 '.$class]) }}>
    <button
        type="button"
        wire:click="changerLangue('fr')"
        class="border-none bg-transparent font-[family-name:var(--font-heading)] text-[11px] font-bold px-2.5 py-1.5 rounded-full transition
            {{ app()->getLocale() === 'fr' ? 'bg-[color:var(--color-sand)] text-[color:var(--color-ink)]' : 'text-[#9AA6C0]' }}"
    >
        FR
    </button>
    <button
        type="button"
        wire:click="changerLangue('ar')"
        class="border-none bg-transparent font-[family-name:var(--font-arabic)] text-[11px] font-bold px-2.5 py-1.5 rounded-full transition
            {{ app()->getLocale() === 'ar' ? 'bg-[color:var(--color-sand)] text-[color:var(--color-ink)]' : 'text-[#9AA6C0]' }}"
    >
        عربي
    </button>
</div>

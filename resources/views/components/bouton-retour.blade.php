@props(['href', 'clair' => false, 'libelle' => null])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition '
        .($clair
            ? 'border border-[color:var(--color-line)] bg-[color:var(--color-card)] text-[color:var(--color-ink)]'
            : 'bg-white/15 hover:bg-white/25 text-white')]) }}
    aria-label="{{ $libelle ?? __('caisse.retour') }}"
>
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rtl:-scale-x-100"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
</a>

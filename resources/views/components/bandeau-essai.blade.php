@php $tenant = auth()->user()?->tenant; @endphp

@if ($tenant?->enLectureSeule())
    <div class="bg-[color:var(--color-rust-deep)] text-white text-xs font-semibold px-4 py-2.5 text-center flex items-center justify-center gap-1.5">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
        {{ __('caisse.essai_expire_bandeau') }}
    </div>
@elseif ($tenant?->statut === 'essai' && $tenant->essai_expire_le)
    @php $jours = max(0, (int) ceil(now()->diffInHours($tenant->essai_expire_le, false) / 24)); @endphp
    <div class="bg-[#E8B85C]/20 text-[#8C6A1F] text-xs font-semibold px-4 py-2.5 text-center flex items-center justify-center gap-1.5">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        {{ __('caisse.essai_jours_restants', ['jours' => $jours]) }}
    </div>
@endif

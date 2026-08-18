@php $tenant = auth()->user()?->tenant; @endphp

@if ($tenant?->enLectureSeule())
    <div class="bg-[color:var(--color-rust-deep)] text-white text-xs font-semibold px-4 py-2.5 text-center">
        ⚠ {{ __('caisse.essai_expire_bandeau') }}
    </div>
@elseif ($tenant?->statut === 'essai' && $tenant->essai_expire_le)
    @php $jours = max(0, (int) ceil(now()->diffInHours($tenant->essai_expire_le, false) / 24)); @endphp
    <div class="bg-[#E8B85C]/20 text-[#8C6A1F] text-xs font-semibold px-4 py-2.5 text-center">
        ⏳ {{ __('caisse.essai_jours_restants', ['jours' => $jours]) }}
    </div>
@endif

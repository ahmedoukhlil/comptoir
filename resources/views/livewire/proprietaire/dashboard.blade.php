<div class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <div class="mx-auto max-w-[980px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            {{-- Barre du haut --}}
            <div class="flex items-center justify-between px-5 pt-5 pb-3 md:px-9">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-[10px] bg-[color:var(--color-ink)] text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                        {{ mb_substr($this->tenant->nom, 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <strong class="block text-sm font-semibold text-[color:var(--color-ink)] truncate">{{ $this->tenant->nom }}</strong>
                        <span class="text-[11px] text-[color:var(--color-ink-soft)]">{{ __('caisse.dashboard_titre') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <x-selecteur-langue-claire />
                    <a href="{{ route('compte.changer-mot-de-passe') }}" class="w-11 h-11 rounded-xl border border-[color:var(--color-line)] bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-ink)]" aria-label="{{ __('caisse.mon_mot_de_passe') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    </a>
                    <button
                        onclick="document.getElementById('logout-form').submit()"
                        class="w-11 h-11 rounded-xl border border-[color:var(--color-line)] bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-ink)]"
                        aria-label="{{ __('caisse.deconnexion') }}"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
            <form id="logout-form" method="POST" action="{{ route('deconnexion') }}" class="hidden">
                @csrf
            </form>

            @if ($this->tenant->statut === 'essai' && $this->tenant->essai_expire_le)
                @php $jours = max(0, (int) ceil(now()->diffInHours($this->tenant->essai_expire_le, false) / 24)); @endphp
                <div class="mx-5 md:mx-9 mb-3 bg-[#FBF3E7] border border-[#EFD9AE] text-[#7A4E0A] rounded-xl px-4 py-3 flex items-center gap-3 text-sm" role="status">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                    <div>{{ __('caisse.essai_jours_restants', ['jours' => $jours]) }}</div>
                </div>
            @endif

            {{-- Carte solde --}}
            <div class="mx-5 md:mx-9 rounded-[20px] p-5 text-white" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-center gap-1.5 text-[11px] uppercase tracking-wide text-white font-semibold mb-1.5">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ __('caisse.dashboard_solde_total') }}
                </div>
                <div class="flex items-baseline gap-2 flex-wrap" dir="ltr">
                    <span class="font-[family-name:var(--font-mono)] font-bold text-[40px] leading-tight tabular-nums tracking-tight">{{ number_format($this->soldeTotal, 0, ',', ' ') }}</span>
                    <span class="text-base text-white/90 font-medium">{{ __('caisse.devise') }}</span>
                </div>
            </div>

            <div class="px-5 md:px-9 pb-6">
                {{-- Cartes stats --}}
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="bg-[color:var(--color-card)] border border-[color:var(--color-line)] rounded-2xl p-4">
                        <div class="flex items-center gap-1.5 text-[11px] uppercase tracking-wide font-semibold text-[color:var(--color-ink-soft)] mb-2">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l4-4 4 4 4-8 4 8"/></svg>
                            {{ __('caisse.dashboard_operations_jour') }}
                        </div>
                        <div class="font-[family-name:var(--font-mono)] text-[26px] font-bold text-[color:var(--color-ink)] tabular-nums text-start" dir="ltr">{{ $this->operationsJourTotal }}</div>
                        <div class="text-[11px] text-[color:var(--color-ink-soft)] mt-1">{{ now()->translatedFormat('d/m/Y') }}</div>
                    </div>
                    <div class="bg-[color:var(--color-card)] border border-[color:var(--color-line)] rounded-2xl p-4">
                        <div class="flex items-center gap-1.5 text-[11px] uppercase tracking-wide font-semibold text-[color:var(--color-ink-soft)] mb-2">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                            {{ __('caisse.dashboard_benefices_cumules') }}
                        </div>
                        <div class="font-[family-name:var(--font-mono)] text-[26px] font-bold text-[color:var(--color-green-deep)] tabular-nums text-start" dir="ltr">+{{ number_format($this->beneficesTotal, 0, ',', ' ') }}</div>
                        <div class="text-[11px] text-[color:var(--color-ink-soft)] mt-1">{{ __('caisse.devise') }}</div>
                    </div>
                </div>

                {{-- Actions primaires --}}
                <div class="mt-5">
                    <p class="text-[11px] uppercase tracking-wide font-bold text-[color:var(--color-ink-soft)] mb-2">{{ __('caisse.section_operations') }}</p>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('proprietaire.alimentation') }}" class="flex items-center gap-3 min-h-[52px] px-4 rounded-2xl bg-[color:var(--color-ink)] text-white font-semibold">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            {{ __('caisse.dashboard_alimenter') }}
                        </a>
                        <a href="{{ route('proprietaire.transfert') }}" class="flex items-center gap-3 min-h-[52px] px-4 rounded-2xl bg-[color:var(--color-card)] text-[color:var(--color-ink)] font-semibold border border-[color:var(--color-line)]">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            {{ __('caisse.dashboard_transferer') }}
                        </a>
                    </div>
                </div>

                {{-- Actions secondaires --}}
                <div class="mt-5">
                    <p class="text-[11px] uppercase tracking-wide font-bold text-[color:var(--color-ink-soft)] mb-2">{{ __('caisse.section_rapports_gestion') }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('proprietaire.rapport') }}" class="flex flex-col items-start justify-center gap-1.5 min-h-[76px] px-4 py-3 rounded-2xl bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink)]">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-secondary)]"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                            <span class="text-[13px] font-semibold leading-tight">{{ __('caisse.dashboard_rapport') }}</span>
                        </a>
                        <a href="{{ route('proprietaire.agents') }}" class="flex flex-col items-start justify-center gap-1.5 min-h-[76px] px-4 py-3 rounded-2xl bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink)]">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-secondary)]"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span class="text-[13px] font-semibold leading-tight">{{ __('caisse.dashboard_gerer_agents') }}</span>
                        </a>
                        <a href="{{ route('proprietaire.operateurs') }}" class="col-span-2 flex flex-col items-start justify-center gap-1.5 min-h-[76px] px-4 py-3 rounded-2xl bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink)]">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-secondary)]"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            <span class="text-[13px] font-semibold leading-tight">{{ __('caisse.dashboard_gerer_operateurs') }}</span>
                        </a>
                    </div>
                </div>

                {{-- Détail par point --}}
                <div class="flex items-center justify-between mt-6 mb-2">
                    <p class="text-[11px] uppercase tracking-wide font-bold text-[color:var(--color-ink-soft)]">{{ __('caisse.dashboard_par_point') }}</p>
                    <span class="text-xs font-semibold bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink-soft)] rounded-full px-2.5 py-0.5">
                        <span dir="ltr">{{ $this->points->count() }}</span> {{ __('caisse.dashboard_points_badge') }}
                    </span>
                </div>

                <div class="flex flex-col gap-3">
                    @foreach ($this->points as $ligne)
                        <div class="bg-[color:var(--color-card)] border border-[color:var(--color-line)] rounded-2xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3.5">
                                <div class="w-10 h-10 rounded-xl bg-[#E9EEFB] text-[color:var(--color-secondary)] flex items-center justify-center flex-shrink-0">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm text-[color:var(--color-ink)] truncate">{{ $ligne->point->nom }}</div>
                                    <div class="flex items-center flex-wrap gap-x-1 text-xs text-[color:var(--color-ink-soft)] mt-0.5" dir="ltr">
                                        <span class="truncate">{{ __('caisse.dashboard_operations_jour') }} :</span>
                                        <span class="font-[family-name:var(--font-mono)] tabular-nums">{{ $ligne->operations_jour }}</span>
                                        <span>·</span>
                                        <span class="font-[family-name:var(--font-mono)] tabular-nums text-[color:var(--color-green-deep)] font-medium">+{{ number_format($ligne->benefices, 0, ',', ' ') }} {{ __('caisse.devise') }}</span>
                                    </div>
                                </div>
                                <div class="font-[family-name:var(--font-mono)] font-semibold text-sm text-[color:var(--color-ink)] tabular-nums flex-shrink-0" dir="ltr">
                                    {{ number_format($ligne->solde, 0, ',', ' ') }}
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1.5 px-4 pb-3.5">
                                @foreach ($ligne->soldes_par_operateur as $so)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink-soft)] rounded-md px-2 py-1" dir="ltr">
                                        <span class="inline-flex items-center gap-1"><x-icone-type-operateur :est-cash="$so['operateur']->est_cash" width="11" height="11" class="flex-shrink-0" /> {{ $so['operateur']->nom }} :</span>
                                        <span class="font-[family-name:var(--font-mono)] tabular-nums">{{ number_format($so['solde'], 0, ',', ' ') }}</span>
                                    </span>
                                @endforeach
                            </div>

                            @if ($ligne->a_ecart)
                                <div class="mx-4 mb-3.5 flex items-center flex-wrap gap-1.5 text-[11px] font-semibold text-[color:var(--color-rust-deep)] bg-[color:var(--color-rust)]/10 rounded-lg px-2.5 py-1.5" dir="ltr">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                    <span>{{ __('caisse.dashboard_alerte_ecart') }}</span>
                                    <span class="font-[family-name:var(--font-mono)] tabular-nums">({{ $ligne->cloture->ecart > 0 ? '+' : '' }}{{ number_format($ligne->cloture->ecart, 0, ',', ' ') }})</span>
                                </div>
                            @endif

                            @if ($ligne->cloture_manquante)
                                <div class="mx-4 mb-3.5 flex items-center gap-1.5 text-[11px] font-semibold text-[#8C6A1F] bg-[#E8B85C]/15 rounded-lg px-2.5 py-1.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                    {{ __('caisse.dashboard_alerte_pas_cloture') }}
                                </div>
                            @endif

                            @if ($ligne->cloture)
                                <div class="mx-4 mb-3.5 flex items-center justify-between gap-2 flex-wrap text-[11px] font-semibold text-[color:var(--color-green-deep)] bg-[color:var(--color-green)]/10 rounded-lg px-2.5 py-1.5">
                                    <span class="flex items-center gap-1.5">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M20 6L9 17l-5-5"/></svg>
                                        {{ __('caisse.dashboard_journee_cloturee') }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="ouvrirConfirmationReouverture({{ $ligne->point->id }})"
                                        class="text-[color:var(--color-ink-soft)] underline hover:text-[color:var(--color-ink)]"
                                    >{{ __('caisse.dashboard_reouvrir_journee') }}</button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if ($pointAReouvrirId)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-[color:var(--color-ink)]/60 backdrop-blur-sm" wire:click="fermerConfirmationReouverture"></div>

            <div class="relative bg-[color:var(--color-paper)] rounded-2xl shadow-xl w-full max-w-sm overflow-hidden {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
                <div class="p-6">
                    <div class="flex items-center justify-center w-11 h-11 rounded-full bg-[color:var(--color-rust-deep)]/10 mb-4">
                        <svg class="w-5 h-5 text-[color:var(--color-rust-deep)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-[color:var(--color-ink)] mb-1.5">
                        {{ __('caisse.dashboard_reouvrir_journee') }}
                    </div>
                    <p class="text-sm text-[color:var(--color-ink-soft)]">
                        {{ __('caisse.dashboard_confirmer_reouverture') }}
                    </p>
                </div>

                <div class="flex border-t border-[color:var(--color-line)]">
                    <button
                        type="button"
                        wire:click="fermerConfirmationReouverture"
                        class="flex-1 text-sm font-semibold py-3.5 text-[color:var(--color-ink-soft)] hover:bg-[color:var(--color-sand-deep)]"
                    >{{ __('caisse.annuler') }}</button>
                    <button
                        type="button"
                        wire:click="reouvrirCloture"
                        wire:loading.attr="disabled"
                        class="flex-1 text-sm font-semibold py-3.5 text-white bg-[color:var(--color-rust-deep)] hover:opacity-90 border-s border-[color:var(--color-line)]"
                    >{{ __('caisse.dashboard_confirmer_reouverture_bouton') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>

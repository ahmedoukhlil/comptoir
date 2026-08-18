<div class="min-h-screen bg-gradient-to-b from-[#0A2242] to-[color:var(--color-ink)] md:bg-none md:bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <div class="mx-auto max-w-[980px] md:py-10 md:px-6">
        <div class="bg-gradient-to-br from-[color:var(--color-sand)] via-[#F1F6FC] to-[color:var(--color-sand-deep)] md:rounded-[22px] md:border md:border-[color:var(--color-line)] md:shadow-2xl overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-[color:var(--color-sand)] px-5 pt-8 pb-6 md:px-9 md:py-6 relative">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base">{{ __('caisse.app_nom') }}</span>
                        <span class="text-xs text-[#9AA6C0]">{{ __('caisse.dashboard_titre') }}</span>
                        <b class="block text-sm font-semibold mt-0.5">{{ $this->tenant->nom }}</b>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-selecteur-langue />
                        <button
                            onclick="document.getElementById('logout-form').submit()"
                            class="text-[11px] font-semibold text-[#9AA6C0] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]"
                        >
                            {{ __('caisse.deconnexion') }}
                        </button>
                    </div>
                </div>
                <form id="logout-form" method="POST" action="{{ route('deconnexion') }}" class="hidden">
                    @csrf
                </form>

                <div class="text-xs font-semibold uppercase tracking-wide text-[#8C97B4] mt-5">{{ __('caisse.dashboard_solde_total') }}</div>
                <div class="font-[family-name:var(--font-mono)] font-bold text-[42px] md:text-[34px] mt-1.5 tabular-nums">
                    {{ number_format($this->soldeTotal, 0, ',', ' ') }}<span class="text-[17px] text-[#8C97B4] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-medium ms-1">{{ __('caisse.devise') }}</span>
                </div>
            </div>

            <div class="px-5 py-6 md:px-9 md:py-8">
                {{-- Résumé --}}
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-[color:var(--color-paper)] rounded-xl p-4 border border-[color:var(--color-line)]">
                        <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase">{{ __('caisse.dashboard_operations_jour') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-xl text-[color:var(--color-ink)] mt-1">{{ $this->operationsJourTotal }}</div>
                    </div>
                    <div class="bg-[color:var(--color-green)]/10 rounded-xl p-4">
                        <div class="text-[11px] font-semibold text-[color:var(--color-green-deep)] uppercase">{{ __('caisse.dashboard_benefices_cumules') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-xl text-[color:var(--color-green-deep)] mt-1">+ {{ number_format($this->beneficesTotal, 0, ',', ' ') }}</div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-2 mb-6">
                    <a href="{{ route('proprietaire.alimentation') }}" class="text-xs font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-ink)] text-white">
                        + {{ __('caisse.dashboard_alimenter') }}
                    </a>
                    <a href="{{ route('proprietaire.transfert') }}" class="text-xs font-semibold px-4 py-2.5 rounded-lg border-[1.5px] border-[color:var(--color-line)] text-[color:var(--color-ink)]">
                        ⇄ {{ __('caisse.dashboard_transferer') }}
                    </a>
                    <a href="{{ route('proprietaire.rapport') }}" class="text-xs font-semibold px-4 py-2.5 rounded-lg border-[1.5px] border-[color:var(--color-line)] text-[color:var(--color-ink)]">
                        {{ __('caisse.dashboard_rapport') }}
                    </a>
                </div>

                {{-- Détail par point --}}
                <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.dashboard_par_point') }}</div>
                <div class="flex flex-col gap-3">
                    @foreach ($this->points as $ligne)
                        <div class="bg-[color:var(--color-paper)] rounded-xl p-4 border border-[color:var(--color-line)]">
                            <div class="flex items-center justify-between">
                                <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-sm text-[color:var(--color-ink)]">{{ $ligne->point->nom }}</div>
                                <div class="font-[family-name:var(--font-mono)] font-bold text-base text-[color:var(--color-ink)]">{{ number_format($ligne->solde, 0, ',', ' ') }} {{ __('caisse.devise') }}</div>
                            </div>
                            <div class="flex items-center gap-4 mt-2 text-[11px] text-[color:var(--color-ink-soft)]">
                                <span>{{ __('caisse.dashboard_operations_jour') }} : {{ $ligne->operations_jour }}</span>
                                <span class="text-[color:var(--color-green-deep)] font-semibold">+ {{ number_format($ligne->benefices, 0, ',', ' ') }} {{ __('caisse.devise') }}</span>
                            </div>

                            <div class="flex flex-wrap gap-1.5 mt-2.5">
                                @foreach ($ligne->soldes_par_operateur as $so)
                                    <span class="text-[10px] font-semibold bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink-soft)] rounded-md px-2 py-1">
                                        {{ $so['operateur']->est_cash ? '💵' : '📱' }} {{ $so['operateur']->nom }} : {{ number_format($so['solde'], 0, ',', ' ') }}
                                    </span>
                                @endforeach
                            </div>

                            @if ($ligne->a_ecart)
                                <div class="mt-2.5 flex items-center gap-1.5 text-[11px] font-semibold text-[color:var(--color-rust-deep)] bg-[color:var(--color-rust)]/10 rounded-lg px-2.5 py-1.5">
                                    <span>⚠</span> {{ __('caisse.dashboard_alerte_ecart') }} ({{ $ligne->cloture->ecart > 0 ? '+' : '' }}{{ number_format($ligne->cloture->ecart, 0, ',', ' ') }})
                                </div>
                            @endif

                            @if ($ligne->cloture_manquante)
                                <div class="mt-2.5 flex items-center gap-1.5 text-[11px] font-semibold text-[#8C6A1F] bg-[#E8B85C]/15 rounded-lg px-2.5 py-1.5">
                                    <span>⏰</span> {{ __('caisse.dashboard_alerte_pas_cloture') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div
    x-data="comptoirSaisie({
        point: {{ Js::from(['id' => $this->point->id]) }},
        operateurs: {{ Js::from($this->operateursPourJs) }},
        soldesServeur: {{ Js::from($this->soldesParOperateur->pluck('solde', 'operateur.id')) }},
        soldeTotalServeur: {{ Js::from($this->solde) }},
    })"
    x-init="init()"
    class="min-h-screen bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
>
    <div class="mx-auto max-w-[980px] md:py-10 md:px-6">
        <div class="md:bg-[color:var(--color-card)] md:rounded-[20px] md:border md:border-[color:var(--color-line)] overflow-hidden">

            {{-- Barre du haut --}}
            <div class="flex items-center justify-between px-5 pt-5 pb-2 md:px-9">
                <div class="min-w-0">
                    <span class="block text-[11px] text-[color:var(--color-ink-soft)]">{{ __('caisse.point') }}</span>
                    <strong class="block text-sm font-semibold text-[color:var(--color-ink)] truncate">{{ $this->point->nom }}</strong>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <x-selecteur-langue-claire />
                    <a href="{{ route('caisse.historique') }}" class="w-10 h-10 rounded-xl border-[1.5px] border-[color:var(--color-line)] bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-ink)] hover:border-[color:var(--color-ink)] hover:bg-[color:var(--color-sand-deep)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition" aria-label="{{ __('caisse.historique_titre') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                    </a>
                    @if (auth()->user()->estProprietaire())
                        <a href="{{ route('proprietaire.agents') }}" class="w-10 h-10 rounded-xl border-[1.5px] border-[color:var(--color-line)] bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-ink)] hover:border-[color:var(--color-ink)] hover:bg-[color:var(--color-sand-deep)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition" aria-label="{{ __('caisse.dashboard_gerer_agents') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </a>
                    @endif
                    <a href="{{ route('compte.changer-mot-de-passe') }}" class="w-10 h-10 rounded-xl border-[1.5px] border-[color:var(--color-line)] bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-ink)] hover:border-[color:var(--color-ink)] hover:bg-[color:var(--color-sand-deep)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition" aria-label="{{ __('caisse.mon_mot_de_passe') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    </a>
                    <button
                        onclick="document.getElementById('logout-form').submit()"
                        class="w-10 h-10 rounded-xl border-[1.5px] border-[color:var(--color-rust)]/40 bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-rust-deep)] hover:border-[color:var(--color-rust-deep)] hover:bg-[color:var(--color-rust)]/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-rust-deep)] transition"
                        aria-label="{{ __('caisse.deconnexion') }}"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
            <form id="logout-form" method="POST" action="{{ route('deconnexion') }}" class="hidden">
                @csrf
            </form>

            {{-- Carte solde --}}
            <div class="mx-5 md:mx-9 rounded-[20px] p-5 text-white" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-center gap-1.5 text-[11px] font-semibold" :class="enLigne ? 'text-[#6FCB9F]' : 'text-[#F0C987]'">
                    <span class="w-1.5 h-1.5 rounded-full" :class="enLigne ? 'bg-[#6FCB9F]' : 'bg-[#F0C987]'"></span>
                    <span x-show="enLigne && enAttente === 0">{{ __('caisse.sync_a_jour') }}</span>
                    <span x-show="!enLigne" x-cloak>{{ __('caisse.sync_hors_ligne') }}</span>
                    <span x-show="enLigne && enAttente > 0" x-cloak x-text="texteSyncEnAttente(enAttente)"></span>
                </div>

                <div class="text-[11px] uppercase tracking-wide font-semibold text-[#B9C6E6] mt-3">{{ __('caisse.solde_label') }}</div>
                <div class="flex items-baseline gap-2 flex-wrap mt-1">
                    <span class="font-[family-name:var(--font-mono)] font-semibold text-[38px] md:text-[34px] leading-none tabular-nums" x-text="formaterMontant(soldeAffiche)"></span>
                    <span class="text-base text-[#B9C6E6] font-medium">{{ __('caisse.devise') }}</span>
                </div>

                <div class="flex flex-wrap gap-2 mt-4">
                    <template x-for="operateur in operateurs" :key="operateur.id">
                        <div class="bg-white/10 rounded-lg px-3 py-1.5">
                            <div class="text-[10px] font-semibold text-[#B9C6E6]" x-text="operateur.nom"></div>
                            <div class="font-[family-name:var(--font-mono)] font-bold text-sm tabular-nums" x-text="formaterMontant(soldeOperateur(operateur.id))"></div>
                        </div>
                    </template>
                </div>

                <a
                    href="{{ route('caisse.cloture') }}"
                    class="flex items-center justify-center gap-2 w-full mt-5 rounded-xl py-3.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-sm text-[color:var(--color-ink)] bg-white hover:bg-[#EDE7DC] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition"
                >
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/></svg>
                    {{ __('caisse.cloture_lien') }}
                </a>
            </div>

            <div class="px-5 pt-6 pb-32 md:px-9 md:py-8 md:pb-8">
                <div class="md:grid md:grid-cols-[1.05fr_0.95fr] md:gap-10 md:items-start">

                    {{-- Colonne formulaire --}}
                    <div>
                        {{-- Bascule dépôt/retrait --}}
                        <div class="flex bg-[color:var(--color-sand-deep)] rounded-2xl p-1.5 gap-1.5">
                            <button
                                type="button"
                                x-on:click="type = 'depot'"
                                class="flex-1 py-4 rounded-xl font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base flex items-center justify-center gap-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                                :class="type === 'depot' ? 'bg-[color:var(--color-green)] text-white shadow-lg shadow-green-900/20' : 'text-[color:var(--color-ink-soft)]'"
                            >
                                <span class="text-lg">↓</span> {{ __('caisse.depot') }}
                            </button>
                            <button
                                type="button"
                                x-on:click="type = 'retrait'"
                                class="flex-1 py-4 rounded-xl font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base flex items-center justify-center gap-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                                :class="type === 'retrait' ? 'bg-[color:var(--color-rust)] text-white shadow-lg shadow-rust-900/20' : 'text-[color:var(--color-ink-soft)]'"
                            >
                                <span class="text-lg">↑</span> {{ __('caisse.retrait') }}
                            </button>
                        </div>

                        {{-- Opérateur --}}
                        <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mt-6 mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.operateur_label') }}</div>
                        <div class="flex gap-2">
                            <template x-for="operateur in operateurs" :key="operateur.id">
                                <button
                                    type="button"
                                    x-on:click="operateurId = operateur.id"
                                    class="flex-1 text-center py-3.5 px-1.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-sm font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                                    :class="operateurId === operateur.id ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-[color:var(--color-sand)]' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                                    x-text="operateur.nom"
                                ></button>
                            </template>
                        </div>

                        {{-- Téléphone client --}}
                        <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mt-6 mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.telephone_label') }}</div>
                        <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl px-4 py-3.5">
                            <span class="text-lg text-[color:var(--color-ink-soft)]">☎</span>
                            <input
                                type="tel"
                                x-model="telephone"
                                inputmode="numeric"
                                maxlength="8"
                                placeholder="{{ __('caisse.telephone_placeholder') }}"
                                dir="ltr"
                                class="flex-1 border-none bg-transparent outline-none font-[family-name:var(--font-mono)] text-lg font-bold text-[color:var(--color-ink)] tracking-wide text-start"
                            >
                        </div>

                        {{-- Optionnel --}}
                        <button
                            type="button"
                            x-on:click="optionnelOuvert = ! optionnelOuvert"
                            class="w-full text-center text-xs font-semibold text-[color:var(--color-ink)] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] py-2.5 mt-2 rounded-xl border-[1.5px] border-dashed border-[color:var(--color-line)] hover:border-[color:var(--color-ink)] hover:bg-[color:var(--color-sand-deep)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                        >
                            {{ __('caisse.ajouter_optionnel') }}
                        </button>
                        <div x-show="optionnelOuvert" x-cloak class="flex flex-col gap-2 mt-1.5">
                            <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-dashed border-[color:var(--color-line)] rounded-xl px-3.5 py-2.5">
                                <span class="text-base text-[color:var(--color-ink-soft)]">👤</span>
                                <input type="text" x-model="clientNom" placeholder="{{ __('caisse.nom_placeholder') }}" class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)]">
                            </div>
                            <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-dashed border-[color:var(--color-line)] rounded-xl px-3.5 py-2.5">
                                <span class="text-base text-[color:var(--color-ink-soft)]">🪪</span>
                                <input type="text" x-model="clientNni" inputmode="numeric" placeholder="{{ __('caisse.nni_placeholder') }}" dir="ltr" class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)] text-start">
                            </div>
                        </div>

                        {{-- Montant --}}
                        <div class="text-center mt-6 px-1">
                            <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-semibold text-[color:var(--color-ink-soft)] tracking-wide">{{ __('caisse.montant_label') }}</div>
                            <div class="font-[family-name:var(--font-mono)] font-bold text-[48px] text-[color:var(--color-ink)] tabular-nums leading-none mt-1.5" x-text="montant !== '' ? formaterMontant(montant) : '0'"></div>
                            <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[12.5px] font-semibold text-[color:var(--color-green-deep)] min-h-[16px] mt-1.5" x-show="commissionActuelle > 0" x-text="'{{ __('caisse.commission_label') }} ' + formaterMontant(commissionActuelle) + ' {{ __('caisse.devise') }}'"></div>
                        </div>

                        {{-- Clavier --}}
                        <div class="grid grid-cols-3 gap-2.5 mt-4 md:max-w-[340px]">
                            <template x-for="chiffre in ['1','2','3','4','5','6','7','8','9']" :key="chiffre">
                                <button
                                    type="button"
                                    x-on:click="taper(chiffre)"
                                    x-text="chiffre"
                                    class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-mono)] text-xl font-bold text-[color:var(--color-ink)] active:scale-95 active:bg-[color:var(--color-sand-deep)] hover:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                                ></button>
                            </template>
                            <button
                                type="button"
                                x-on:click="effacer()"
                                class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-bold text-[color:var(--color-ink-soft)] hover:border-[color:var(--color-ink)] hover:text-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                            >{{ __('caisse.effacer') }}</button>
                            <button
                                type="button"
                                x-on:click="taper('0')"
                                class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-mono)] text-xl font-bold text-[color:var(--color-ink)] active:scale-95 active:bg-[color:var(--color-sand-deep)] hover:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                            >0</button>
                            <button
                                type="button"
                                x-on:click="taper('000')"
                                class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-bold text-[color:var(--color-ink-soft)] hover:border-[color:var(--color-ink)] hover:text-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition"
                            >000</button>
                        </div>

                        {{-- Confirmer --}}
                        <button
                            type="button"
                            x-on:click="confirmer()"
                            :disabled="enConfirmation"
                            class="w-full md:max-w-[340px] mt-4 rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white flex items-center justify-center gap-2 transition disabled:opacity-60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            :class="type === 'depot' ? 'bg-[color:var(--color-green-deep)] shadow-lg shadow-green-900/25' : 'bg-[color:var(--color-rust-deep)] shadow-lg shadow-rust-900/25'"
                        >
                            {{ __('caisse.confirmer') }}
                        </button>
                        <p x-show="erreurLocale" x-cloak x-text="erreurLocale" class="text-xs text-[color:var(--color-rust-deep)] mt-1.5 text-center"></p>
                    </div>

                    {{-- Colonne historique --}}
                    <div class="mt-8 md:mt-0 md:border-s md:border-[color:var(--color-line)] md:ps-9">
                        <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.aujourdhui') }}</div>
                        <div class="flex gap-4 mb-1.5">
                            <span class="flex items-center gap-1.5 text-[11px] font-semibold text-[color:var(--color-ink-soft)] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">
                                <span class="w-2 h-2 rounded-full bg-[color:var(--color-green)]"></span> {{ __('caisse.entree') }}
                            </span>
                            <span class="flex items-center gap-1.5 text-[11px] font-semibold text-[color:var(--color-ink-soft)] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">
                                <span class="w-2 h-2 rounded-full bg-[color:var(--color-rust)]"></span> {{ __('caisse.sortie') }}
                            </span>
                        </div>

                        <div class="pb-28 md:pb-2 md:max-h-[520px] md:overflow-y-auto">
                            <template x-for="operation in operationsLocalesAffichage" :key="operation.uuid_client">
                                <div class="flex items-center gap-3 py-3 border-b border-dashed border-[color:var(--color-line)] opacity-70">
                                    <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :class="operation.type === 'depot' ? 'bg-[color:var(--color-green)]' : 'bg-[color:var(--color-rust)]'"></div>
                                    <div class="flex-1">
                                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-sm font-bold text-[color:var(--color-ink)]">
                                            <span x-text="(operation.type === 'depot' ? '{{ __('caisse.recu') }}' : '{{ __('caisse.donne') }}') + ' — ' + operation.operateur_nom"></span>
                                        </div>
                                        <div class="text-[11px] text-[color:var(--color-ink-soft)] mt-0.5 flex items-center gap-1" dir="ltr">
                                            <span x-text="operation.heure"></span> · <span x-text="operation.client_telephone"></span>
                                            <span class="text-[#B8853C]" x-text="'⏳ ' + (window.ComptoirTraductions?.syncBadgeAttente ?? '')"></span>
                                        </div>
                                    </div>
                                    <div class="font-[family-name:var(--font-mono)] font-bold text-[15px]" dir="ltr" :class="operation.type === 'depot' ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]'">
                                        <span x-text="(operation.type === 'depot' ? '+' : '−') + ' ' + formaterMontant(operation.montant)"></span>
                                    </div>
                                </div>
                            </template>
                            @forelse ($this->operationsDuJour as $operation)
                                <div class="flex items-center gap-3 py-3 border-b border-dashed border-[color:var(--color-line)]">
                                    <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $operation->type === 'depot' ? 'bg-[color:var(--color-green)]' : 'bg-[color:var(--color-rust)]' }}"></div>
                                    <div class="flex-1">
                                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-sm font-bold text-[color:var(--color-ink)]">
                                            {{ $operation->type === 'depot' ? __('caisse.recu') : __('caisse.donne') }} — {{ $operation->operateur->nom }}
                                        </div>
                                        <div class="text-[11px] text-[color:var(--color-ink-soft)] mt-0.5" dir="ltr">
                                            {{ $operation->created_at->format('H:i') }} · {{ $operation->client_telephone }}
                                        </div>
                                    </div>
                                    <div class="font-[family-name:var(--font-mono)] font-bold text-[15px] {{ $operation->type === 'depot' ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]' }}" dir="ltr">
                                        {{ $operation->type === 'depot' ? '+' : '−' }} {{ number_format($operation->montant, 0, ',', ' ') }}
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-[color:var(--color-ink-soft)] py-4" x-show="operationsLocalesAffichage.length === 0">{{ __('caisse.aucune_operation') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Barre résumé --}}
            <div class="fixed md:static start-3.5 end-3.5 bottom-4 md:mx-9 md:mb-8 bg-[color:var(--color-ink)] rounded-[18px] px-4.5 py-4 flex justify-between items-center shadow-2xl">
                <div>
                    <div class="text-[10px] tracking-wide font-semibold text-[#8C97B4] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.benefice_label') }}</div>
                    <div class="font-[family-name:var(--font-mono)] font-bold text-[17px] text-[#6FCB9F] mt-0.5">
                        + {{ number_format($this->beneficeDuJour, 0, ',', ' ') }} {{ __('caisse.devise') }}
                    </div>
                </div>
                <div>
                    <div class="text-[10px] tracking-wide font-semibold text-[#8C97B4] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.operations_label') }}</div>
                    <div class="font-[family-name:var(--font-mono)] font-bold text-[17px] text-[color:var(--color-sand)] mt-0.5">
                        {{ $this->operationsDuJour->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

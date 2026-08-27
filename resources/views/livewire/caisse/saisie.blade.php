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
                    <span class="block text-[11px] text-[color:var(--color-ink-soft)] mt-0.5">{{ now()->translatedFormat('l d F Y') }}</span>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <x-selecteur-langue-claire />
                    <a href="{{ route('caisse.historique') }}" class="w-11 h-11 rounded-xl border-[1.5px] border-[color:var(--color-line)] bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-ink)] hover:border-[color:var(--color-ink)] hover:bg-[color:var(--color-sand-deep)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition" aria-label="{{ __('caisse.historique_titre') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                    </a>
                    @if (auth()->user()->estProprietaire())
                        <a href="{{ route('proprietaire.agents') }}" class="w-11 h-11 rounded-xl border-[1.5px] border-[color:var(--color-line)] bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-ink)] hover:border-[color:var(--color-ink)] hover:bg-[color:var(--color-sand-deep)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition" aria-label="{{ __('caisse.dashboard_gerer_agents') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </a>
                    @endif
                    <a href="{{ route('compte.changer-mot-de-passe') }}" class="w-11 h-11 rounded-xl border-[1.5px] border-[color:var(--color-line)] bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-ink)] hover:border-[color:var(--color-ink)] hover:bg-[color:var(--color-sand-deep)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)] transition" aria-label="{{ __('caisse.mon_mot_de_passe') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    </a>
                    <button
                        onclick="document.getElementById('logout-form').submit()"
                        class="w-11 h-11 rounded-xl border-[1.5px] border-[color:var(--color-rust)]/40 bg-[color:var(--color-card)] flex items-center justify-center text-[color:var(--color-rust-deep)] hover:border-[color:var(--color-rust-deep)] hover:bg-[color:var(--color-rust)]/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-rust-deep)] transition"
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
                <div class="flex items-center gap-1.5 text-[11px] font-semibold" :class="enLigne ? 'text-[#86EFAC]' : 'text-[#FDE68A]'">
                    <span class="w-1.5 h-1.5 rounded-full" :class="enLigne ? 'bg-[#86EFAC]' : 'bg-[#FDE68A]'"></span>
                    <span x-show="enLigne && enAttente === 0">{{ __('caisse.sync_a_jour') }}</span>
                    <span x-show="!enLigne" x-cloak>{{ __('caisse.sync_hors_ligne') }}</span>
                    <span x-show="enLigne && enAttente > 0" x-cloak x-text="texteSyncEnAttente(enAttente)"></span>
                </div>

                <div class="text-[11px] uppercase tracking-wide font-semibold text-white mt-3">{{ __('caisse.solde_label') }}</div>
                <div class="flex items-baseline gap-2 flex-wrap mt-1">
                    <span class="font-[family-name:var(--font-mono)] font-semibold text-[38px] md:text-[34px] leading-none tabular-nums" x-text="formaterMontant(soldeAffiche)"></span>
                    <span class="text-base text-white font-medium">{{ __('caisse.devise') }}</span>
                </div>

                <div class="flex flex-wrap gap-2 mt-4">
                    <template x-for="operateur in operateurs" :key="operateur.id">
                        <div class="bg-white/10 rounded-lg px-3 py-1.5">
                            <div class="text-[10px] font-semibold text-white" x-text="operateur.nom"></div>
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
                        <div class="flex flex-col bg-[color:var(--color-sand-deep)] rounded-2xl p-1.5 gap-1.5" role="radiogroup" aria-label="{{ __('caisse.operateur_label') }}">
                            <button
                                type="button"
                                role="radio"
                                :aria-checked="(type === 'depot').toString()"
                                x-on:click="type = 'depot'"
                                class="w-full py-4 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base flex items-center justify-center gap-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                                :class="type === 'depot' ? 'border-transparent bg-[color:var(--color-green)] text-white shadow-lg shadow-green-900/20' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                            >
                                <span class="text-lg">↓</span> {{ __('caisse.depot') }}
                            </button>
                            <div class="flex gap-1.5">
                                <button
                                    type="button"
                                    role="radio"
                                    :aria-checked="(type === 'retrait').toString()"
                                    x-on:click="type = 'retrait'"
                                    class="flex-1 py-3.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-sm flex items-center justify-center gap-1.5 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                                    :class="type === 'retrait' ? 'border-transparent bg-[color:var(--color-rust)] text-white shadow-lg shadow-rust-900/20' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                                >
                                    <span class="text-base">↑</span> {{ __('caisse.retrait') }}
                                </button>
                                <button
                                    type="button"
                                    role="radio"
                                    :aria-checked="(type === 'retrait_beneficiaire').toString()"
                                    x-on:click="type = 'retrait_beneficiaire'"
                                    class="flex-1 py-3.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-sm flex items-center justify-center gap-1.5 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]"
                                    :class="type === 'retrait_beneficiaire' ? 'border-transparent bg-[color:var(--color-rust)] text-white shadow-lg shadow-rust-900/20' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]'"
                                >
                                    <span class="text-base">↑</span> {{ __('caisse.retrait_beneficiaire') }}
                                </button>
                            </div>
                        </div>

                        {{-- Opérateur --}}
                        <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] mt-6 mb-2.5 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.operateur_label') }}</div>
                        <div class="flex gap-2" role="radiogroup" aria-label="{{ __('caisse.operateur_label') }}">
                            <template x-for="operateur in operateurs" :key="operateur.id">
                                <button
                                    type="button"
                                    role="radio"
                                    :aria-checked="(operateurId === operateur.id).toString()"
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
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-ink-soft)] flex-shrink-0"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
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
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-ink-soft)] flex-shrink-0"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <input type="text" x-model="clientNom" placeholder="{{ __('caisse.nom_placeholder') }}" class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)]">
                            </div>
                            <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-dashed border-[color:var(--color-line)] rounded-xl px-3.5 py-2.5">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[color:var(--color-ink-soft)] flex-shrink-0"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="6" y1="15" x2="10" y2="15"/><circle cx="7" cy="10" r="1.5"/><line x1="12" y1="9" x2="18" y2="9"/><line x1="12" y1="12" x2="18" y2="12"/></svg>
                                <input type="text" x-model="clientNni" inputmode="numeric" placeholder="{{ __('caisse.nni_placeholder') }}" dir="ltr" class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)] text-start">
                            </div>
                        </div>

                        {{-- Montant --}}
                        <div class="mt-6 px-1">
                            <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-semibold text-[color:var(--color-ink-soft)] tracking-wide text-center">{{ __('caisse.montant_label') }}</div>
                            <div class="flex items-center gap-2.5 bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl px-4 py-3.5 mt-1.5 focus-within:border-[color:var(--color-ink)]">
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    x-model="montant"
                                    x-on:input="montant = $event.target.value.replace(/\D/g, '').slice(0, 9)"
                                    placeholder="0"
                                    dir="ltr"
                                    class="flex-1 min-w-0 border-none bg-transparent outline-none font-[family-name:var(--font-mono)] font-bold text-[32px] text-[color:var(--color-ink)] tabular-nums text-end placeholder:text-[color:var(--color-ink-soft)]/50"
                                >
                                <span class="text-sm font-semibold text-[color:var(--color-ink-soft)] flex-shrink-0">{{ __('caisse.devise') }}</span>
                            </div>
                            <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[12.5px] font-semibold text-[color:var(--color-green-deep)] min-h-[16px] mt-1.5 text-center" x-show="commissionActuelle > 0" x-text="'{{ __('caisse.commission_label') }} ' + formaterMontant(commissionActuelle) + ' {{ __('caisse.devise') }}'"></div>
                        </div>

                        {{-- Confirmer --}}
                        <button
                            type="button"
                            x-on:click="ouvrirConfirmation()"
                            :disabled="enConfirmation || ! formulaireValide"
                            class="w-full md:max-w-[340px] mt-4 rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white flex items-center justify-center gap-2 transition disabled:opacity-60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            :class="! type ? 'bg-[color:var(--color-ink-soft)]' : (type === 'depot' ? 'bg-[color:var(--color-green-deep)] shadow-lg shadow-green-900/25' : 'bg-[color:var(--color-rust-deep)] shadow-lg shadow-rust-900/25')"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M20 6L9 17l-5-5"/></svg>
                            {{ __('caisse.confirmer') }}
                        </button>
                        <p x-show="erreurLocale" x-cloak x-text="erreurLocale" role="alert" class="text-xs text-[color:var(--color-rust-deep)] mt-1.5 text-center"></p>
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
                                            <span x-text="libelleType(operation.type) + ' · ' + operation.operateur_nom"></span>
                                        </div>
                                        <div class="text-[11px] text-[color:var(--color-ink-soft)] mt-0.5 flex items-center gap-1" dir="ltr">
                                            <span x-text="operation.heure"></span> · <span x-text="operation.client_telephone"></span>
                                            <span class="inline-flex items-center gap-0.5 text-[#B8853C]">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                                <span x-text="window.ComptoirTraductions?.syncBadgeAttente ?? @js(__('caisse.sync_badge_attente'))"></span>
                                            </span>
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
                                            {{ $operation->libelleType() }} · {{ $operation->operateur->nom }}
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
                    <div class="text-[10px] tracking-wide font-semibold text-white/90 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.benefice_label') }}</div>
                    <div class="font-[family-name:var(--font-mono)] font-bold text-[17px] text-[#86EFAC] mt-0.5">
                        + {{ number_format($this->beneficeDuJour, 0, ',', ' ') }} {{ __('caisse.devise') }}
                    </div>
                </div>
                <div>
                    <div class="text-[10px] tracking-wide font-semibold text-white/90 font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.operations_label') }}</div>
                    <div class="font-[family-name:var(--font-mono)] font-bold text-[17px] text-[color:var(--color-sand)] mt-0.5">
                        {{ $this->operationsDuJour->count() }}
                    </div>
                </div>
            </div>

            {{-- Modale de confirmation avant opération --}}
            <div
                x-show="confirmationOuverte"
                x-cloak
                x-on:keydown.escape.window="fermerConfirmation()"
                class="fixed inset-0 z-50 flex items-center justify-center px-4"
            >
                <div class="absolute inset-0 bg-[color:var(--color-ink)]/60 backdrop-blur-sm" x-on:click="fermerConfirmation()"></div>

                <div
                    x-show="confirmationOuverte"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    role="dialog"
                    aria-modal="true"
                    class="relative bg-[color:var(--color-paper)] rounded-2xl shadow-xl w-full max-w-sm overflow-hidden {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
                >
                    <div class="p-6">
                        <div class="font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-[color:var(--color-ink)] mb-1.5">
                            {{ __('caisse.confirmation_titre') }}
                        </div>
                        <p class="text-sm text-[color:var(--color-ink-soft)] mb-4">
                            {{ __('caisse.confirmation_texte') }}
                        </p>

                        <div class="divide-y divide-dashed divide-[color:var(--color-line)] border-y border-dashed border-[color:var(--color-line)]">
                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-sm text-[color:var(--color-ink-soft)]">{{ __('caisse.confirmation_type') }}</span>
                                <span class="text-sm font-bold" :class="type === 'depot' ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]'" x-text="libelleType(type)"></span>
                            </div>
                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-sm text-[color:var(--color-ink-soft)]">{{ __('caisse.confirmation_operateur') }}</span>
                                <span class="text-sm font-bold text-[color:var(--color-ink)]" x-text="operateurs.find(o => o.id === operateurId)?.nom ?? ''"></span>
                            </div>
                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-sm text-[color:var(--color-ink-soft)]">{{ __('caisse.confirmation_telephone') }}</span>
                                <span class="text-sm font-bold text-[color:var(--color-ink)]" dir="ltr" x-text="telephone"></span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm font-bold text-[color:var(--color-ink)]">{{ __('caisse.confirmation_montant') }}</span>
                                <span class="text-lg font-[family-name:var(--font-mono)] font-bold text-[color:var(--color-ink)]" dir="ltr" x-text="formaterMontant(montant) + ' ' + @js(__('caisse.devise'))"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex border-t border-[color:var(--color-line)]">
                        <button
                            type="button"
                            x-on:click="fermerConfirmation()"
                            class="flex-1 text-sm font-semibold py-3.5 text-[color:var(--color-ink-soft)] hover:bg-[color:var(--color-sand-deep)]"
                        >{{ __('caisse.confirmation_annuler') }}</button>
                        <button
                            type="button"
                            x-on:click="confirmer()"
                            :disabled="enConfirmation"
                            class="flex-1 text-sm font-semibold py-3.5 text-white hover:opacity-90 border-s border-[color:var(--color-line)] disabled:opacity-60"
                            :class="type === 'depot' ? 'bg-[color:var(--color-green-deep)]' : 'bg-[color:var(--color-rust-deep)]'"
                        ><span x-show="! enConfirmation">{{ __('caisse.confirmation_valider') }}</span><span x-show="enConfirmation" x-cloak>…</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

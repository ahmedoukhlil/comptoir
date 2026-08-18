<div
    x-data="comptoirSaisie({
        point: {{ Js::from(['id' => $this->point->id]) }},
        operateurs: {{ Js::from($this->operateursPourJs) }},
        soldesServeur: {{ Js::from($this->soldesParOperateur->pluck('solde', 'operateur.id')) }},
        soldeTotalServeur: {{ Js::from($this->solde) }},
    })"
    x-init="init()"
    class="min-h-screen bg-gradient-to-b from-[#0A2242] to-[color:var(--color-ink)] md:bg-none md:bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}"
>
    <div class="mx-auto max-w-[980px] md:py-10 md:px-6">
        <div class="bg-gradient-to-br from-[color:var(--color-sand)] via-[#F1F6FC] to-[color:var(--color-sand-deep)] md:rounded-[22px] md:border md:border-[color:var(--color-line)] md:shadow-2xl overflow-hidden">

            {{-- Bandeau / solde --}}
            <div class="bg-[color:var(--color-ink)] text-[color:var(--color-sand)] px-5 pt-8 pb-6 md:px-9 md:py-6 relative">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="block font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base">{{ __('caisse.app_nom') }}</span>
                        <span class="text-xs text-[#9AA6C0]">{{ __('caisse.point') }}</span>
                        <b class="block text-sm font-semibold mt-0.5">{{ $this->point->nom }}</b>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-selecteur-langue />
                        <a href="{{ route('caisse.historique') }}" class="text-[11px] font-semibold text-[#9AA6C0] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">
                            {{ __('caisse.historique_titre') }}
                        </a>
                        <a href="{{ route('caisse.cloture') }}" class="text-[11px] font-semibold text-[#9AA6C0] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">
                            {{ __('caisse.cloture_lien') }}
                        </a>
                        @if (auth()->user()->estProprietaire())
                            <a href="{{ route('proprietaire.agents') }}" class="text-[11px] font-semibold text-[#9AA6C0] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">
                                {{ __('caisse.dashboard_gerer_agents') }}
                            </a>
                        @endif
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

                {{-- Indicateur de synchronisation --}}
                <div class="flex items-center gap-1.5 mt-3 text-[11px] font-semibold" :class="enLigne ? 'text-[#6FCB9F]' : 'text-[#E8B85C]'">
                    <span class="w-1.5 h-1.5 rounded-full" :class="enLigne ? 'bg-[#6FCB9F]' : 'bg-[#E8B85C]'"></span>
                    <span x-show="enLigne && enAttente === 0">{{ __('caisse.sync_a_jour') }}</span>
                    <span x-show="!enLigne" x-cloak>{{ __('caisse.sync_hors_ligne') }}</span>
                    <span x-show="enLigne && enAttente > 0" x-cloak x-text="texteSyncEnAttente(enAttente)"></span>
                </div>

                <div class="text-xs font-semibold uppercase tracking-wide text-[#8C97B4] mt-4">{{ __('caisse.solde_label') }}</div>
                <div class="font-[family-name:var(--font-mono)] font-bold text-[42px] md:text-[34px] mt-1.5 tabular-nums">
                    <span x-text="formaterMontant(soldeAffiche)"></span><span class="text-[17px] text-[#8C97B4] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-medium ms-1">{{ __('caisse.devise') }}</span>
                </div>

                <div class="flex flex-wrap gap-2 mt-4">
                    <template x-for="operateur in operateurs" :key="operateur.id">
                        <div class="bg-white/10 rounded-lg px-3 py-1.5">
                            <div class="text-[10px] font-semibold text-[#9AA6C0]" x-text="operateur.nom"></div>
                            <div class="font-[family-name:var(--font-mono)] font-bold text-sm tabular-nums" x-text="formaterMontant(soldeOperateur(operateur.id))"></div>
                        </div>
                    </template>
                </div>
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
                                class="flex-1 py-4 rounded-xl font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base flex items-center justify-center gap-2 transition"
                                :class="type === 'depot' ? 'bg-[color:var(--color-green)] text-white shadow-lg shadow-green-900/20' : 'text-[color:var(--color-ink-soft)]'"
                            >
                                <span class="text-lg">↓</span> {{ __('caisse.depot') }}
                            </button>
                            <button
                                type="button"
                                x-on:click="type = 'retrait'"
                                class="flex-1 py-4 rounded-xl font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base flex items-center justify-center gap-2 transition"
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
                                    class="flex-1 text-center py-3.5 px-1.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-sm font-bold transition"
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
                            class="w-full text-center text-xs font-semibold text-[color:var(--color-ink-soft)] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] py-2 mt-2"
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
                                    class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-mono)] text-xl font-bold text-[color:var(--color-ink)] active:scale-95 active:bg-[color:var(--color-sand-deep)] transition"
                                ></button>
                            </template>
                            <button
                                type="button"
                                x-on:click="effacer()"
                                class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-bold text-[color:var(--color-ink-soft)]"
                            >{{ __('caisse.effacer') }}</button>
                            <button
                                type="button"
                                x-on:click="taper('0')"
                                class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-mono)] text-xl font-bold text-[color:var(--color-ink)] active:scale-95 active:bg-[color:var(--color-sand-deep)] transition"
                            >0</button>
                            <button
                                type="button"
                                x-on:click="taper('000')"
                                class="bg-[color:var(--color-paper)] border-[1.5px] border-[color:var(--color-line)] rounded-2xl py-4.5 text-center font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] text-[13px] font-bold text-[color:var(--color-ink-soft)]"
                            >000</button>
                        </div>

                        {{-- Confirmer --}}
                        <button
                            type="button"
                            x-on:click="confirmer()"
                            :disabled="enConfirmation"
                            class="w-full md:max-w-[340px] mt-4 rounded-2xl py-[19px] font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold text-base text-white flex items-center justify-center gap-2 transition disabled:opacity-60"
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
                                    <div class="font-[family-name:var(--font-mono)] font-bold text-[15px]" :class="operation.type === 'depot' ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]'">
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
                                    <div class="font-[family-name:var(--font-mono)] font-bold text-[15px] {{ $operation->type === 'depot' ? 'text-[color:var(--color-green-deep)]' : 'text-[color:var(--color-rust-deep)]' }}">
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

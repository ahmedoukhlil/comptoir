<div class="min-h-screen">
    <div class="mx-auto max-w-[900px] py-10 px-6">
        <div class="bg-[color:var(--color-paper)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-white px-6 py-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="block font-[family-name:var(--font-heading)] font-bold text-base">{{ __('admin.titre') }}</span>
                    <span class="text-xs text-white/90">{{ __('admin.operateurs_titre') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.tenants.index') }}" class="text-xs font-semibold text-white bg-white/10 hover:bg-white/20 rounded-lg px-3 py-2 font-[family-name:var(--font-heading)]">
                        {{ __('admin.tenants_titre') }}
                    </a>
                    <a href="{{ route('compte.changer-mot-de-passe') }}" class="text-xs font-semibold text-white bg-white/10 hover:bg-white/20 rounded-lg px-3 py-2 font-[family-name:var(--font-heading)]">
                        {{ __('admin.mon_mot_de_passe') }}
                    </a>
                    <button
                        onclick="document.getElementById('logout-form').submit()"
                        class="text-xs font-semibold text-white bg-white/10 hover:bg-white/20 rounded-lg px-3 py-2 font-[family-name:var(--font-heading)]"
                    >{{ __('admin.deconnexion') }}</button>
                </div>
                <form id="logout-form" method="POST" action="{{ route('deconnexion') }}" class="hidden">
                    @csrf
                </form>
            </div>

            <div class="p-6">
                <div class="flex justify-end mb-5">
                    <a href="{{ route('admin.operateurs.create') }}" class="text-sm font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-ink)] text-white">
                        + {{ __('caisse.operateurs_nouveau') }}
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase border-b border-[color:var(--color-line)]">
                                <th class="py-2.5 pe-4">{{ __('caisse.operateur_nom_label') }}</th>
                                <th class="py-2.5 pe-4">{{ __('caisse.operateur_type_label') }}</th>
                                <th class="py-2.5 pe-4">{{ __('admin.colonne_statut') }}</th>
                                <th class="py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-line)]">
                            @forelse ($this->operateurs as $operateur)
                                <tr>
                                    <td class="py-3 pe-4 font-semibold text-[color:var(--color-ink)]">{{ $operateur->nom }}</td>
                                    <td class="py-3 pe-4 text-[color:var(--color-ink-soft)]">{{ $operateur->est_cash ? __('caisse.operateur_type_cash') : __('caisse.operateur_type_mobile') }}</td>
                                    <td class="py-3 pe-4">
                                        <span class="text-xs font-semibold px-2 py-1 rounded-md {{ $operateur->actif ? 'bg-[color:var(--color-green)]/10 text-[color:var(--color-green-deep)]' : 'bg-[color:var(--color-rust)]/10 text-[color:var(--color-rust-deep)]' }}">
                                            {{ $operateur->actif ? __('caisse.operateur_activer') : __('caisse.operateur_desactiver') }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-end">
                                        <div class="flex items-center justify-end gap-2">
                                            @unless ($operateur->est_cash)
                                                <a href="{{ route('admin.operateurs.edit', $operateur) }}" class="text-xs font-semibold text-[color:var(--color-ink)] underline">
                                                    {{ __('caisse.operateur_modifier') }}
                                                </a>
                                            @endunless
                                            <button
                                                type="button"
                                                wire:click="basculerActif({{ $operateur->id }})"
                                                class="text-xs font-semibold text-[color:var(--color-ink-soft)] underline"
                                            >{{ $operateur->actif ? __('caisse.operateur_desactiver') : __('caisse.operateur_activer') }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-[color:var(--color-ink-soft)]">{{ __('caisse.operateurs_aucun') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

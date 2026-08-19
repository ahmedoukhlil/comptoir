<div class="min-h-screen">
    <div class="mx-auto max-w-[1100px] py-10 px-6">
        <div class="bg-[color:var(--color-paper)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-white px-6 py-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="block font-[family-name:var(--font-heading)] font-bold text-base">{{ __('admin.titre') }}</span>
                    <span class="text-xs text-[#C7D2E3]">{{ __('admin.tenants_titre') }}</span>
                </div>
                <div class="flex items-center gap-2">
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
                {{-- Filtres --}}
                <div class="flex flex-wrap gap-3 mb-5">
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="recherche"
                        placeholder="{{ __('admin.rechercher_tenant') }}"
                        class="flex-1 min-w-[200px] border-[1.5px] border-[color:var(--color-line)] rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]"
                    >
                    <select wire:model.live="plan" class="border-[1.5px] border-[color:var(--color-line)] rounded-lg px-3.5 py-2.5 text-sm">
                        <option value="">{{ __('admin.tous') }} — {{ __('admin.colonne_plan') }}</option>
                        <option value="solo">{{ __('admin.plan_solo') }}</option>
                        <option value="reseau">{{ __('admin.plan_reseau') }}</option>
                        <option value="entreprise">{{ __('admin.plan_entreprise') }}</option>
                    </select>
                    <select wire:model.live="statut" class="border-[1.5px] border-[color:var(--color-line)] rounded-lg px-3.5 py-2.5 text-sm">
                        <option value="">{{ __('admin.tous') }} — {{ __('admin.colonne_statut') }}</option>
                        <option value="essai">{{ __('admin.statut_essai') }}</option>
                        <option value="actif">{{ __('admin.statut_actif') }}</option>
                        <option value="lecture_seule">{{ __('admin.statut_lecture_seule') }}</option>
                        <option value="suspendu">{{ __('admin.statut_suspendu') }}</option>
                    </select>
                    <a href="{{ route('admin.tenants.create') }}" class="text-sm font-semibold px-4 py-2.5 rounded-lg bg-[color:var(--color-ink)] text-white">
                        + {{ __('admin.nouveau_tenant') }}
                    </a>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase border-b border-[color:var(--color-line)]">
                                <th class="py-2.5 pe-4">{{ __('admin.colonne_nom') }}</th>
                                <th class="py-2.5 pe-4">{{ __('admin.colonne_plan') }}</th>
                                <th class="py-2.5 pe-4">{{ __('admin.colonne_statut') }}</th>
                                <th class="py-2.5 pe-4 text-end">{{ __('admin.colonne_points') }}</th>
                                <th class="py-2.5 pe-4 text-end">{{ __('admin.colonne_agents') }}</th>
                                <th class="py-2.5 pe-4">{{ __('admin.colonne_cree_le') }}</th>
                                <th class="py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-line)]">
                            @forelse ($this->tenants as $tenant)
                                <tr>
                                    <td class="py-3 pe-4 font-semibold text-[color:var(--color-ink)]">{{ $tenant->nom }}</td>
                                    <td class="py-3 pe-4">
                                        <span class="text-xs font-semibold px-2 py-1 rounded-md bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink)]">
                                            {{ __('admin.plan_'.$tenant->plan) }}
                                        </span>
                                    </td>
                                    <td class="py-3 pe-4">
                                        <span class="text-xs font-semibold px-2 py-1 rounded-md
                                            {{ match($tenant->statut) {
                                                'actif' => 'bg-[color:var(--color-green)]/10 text-[color:var(--color-green-deep)]',
                                                'essai' => 'bg-[#E8B85C]/20 text-[#8C6A1F]',
                                                'lecture_seule', 'suspendu' => 'bg-[color:var(--color-rust)]/10 text-[color:var(--color-rust-deep)]',
                                                default => '',
                                            } }}">
                                            {{ __('admin.statut_'.$tenant->statut) }}
                                        </span>
                                    </td>
                                    <td class="py-3 pe-4 text-end font-[family-name:var(--font-mono)]">{{ $tenant->points_count }}</td>
                                    <td class="py-3 pe-4 text-end font-[family-name:var(--font-mono)]">{{ $tenant->agents_count }}</td>
                                    <td class="py-3 pe-4 text-[color:var(--color-ink-soft)]">{{ $tenant->created_at->format('d/m/Y') }}</td>
                                    <td class="py-3">
                                        <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-xs font-semibold text-[color:var(--color-ink)] underline">
                                            {{ __('admin.voir') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-[color:var(--color-ink-soft)]">{{ __('admin.aucun_tenant') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $this->tenants->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

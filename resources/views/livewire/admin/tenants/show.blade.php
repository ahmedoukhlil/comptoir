<div class="min-h-screen">
    <div class="mx-auto max-w-[900px] py-10 px-6">
        <div class="bg-[color:var(--color-paper)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-white px-6 py-5">
                <a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-white/90 hover:text-white underline decoration-white/40 underline-offset-2"><span aria-hidden="true">&larr;</span> {{ __('admin.retour_liste') }}</a>
                <div class="mt-1.5 font-[family-name:var(--font-heading)] font-bold text-lg">{{ $this->tenant->nom }}</div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-[color:var(--color-sand-deep)] rounded-xl p-4">
                        <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase mb-2">{{ __('admin.changer_plan') }}</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach (['solo', 'reseau', 'entreprise'] as $plan)
                                <button
                                    type="button"
                                    wire:click="changerPlan('{{ $plan }}')"
                                    class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ $this->tenant->plan === $plan ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                                >{{ __('admin.plan_'.$plan) }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-[color:var(--color-sand-deep)] rounded-xl p-4">
                        <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase mb-2">{{ __('admin.changer_statut') }}</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach (['essai', 'actif', 'lecture_seule', 'suspendu'] as $statut)
                                <button
                                    type="button"
                                    wire:click="changerStatut('{{ $statut }}')"
                                    class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ $this->tenant->statut === $statut ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-paper)] text-[color:var(--color-ink-soft)]' }}"
                                >{{ __('admin.statut_'.$statut) }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($this->tenant->statut === 'essai' && $this->tenant->essai_expire_le)
                    <p class="text-xs text-[color:var(--color-ink-soft)] mb-6">
                        {{ __('admin.essai_expire_le') }} : {{ $this->tenant->essai_expire_le->format('d/m/Y H:i') }}
                    </p>
                @endif

                <div class="flex justify-end mb-6">
                    <button
                        type="button"
                        wire:click="supprimer"
                        wire:confirm="{{ __('admin.confirmer_suppression_tenant') }}"
                        class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] border-[color:var(--color-rust-deep)] text-[color:var(--color-rust-deep)] hover:bg-[color:var(--color-rust-deep)] hover:text-white"
                    >{{ __('admin.supprimer_tenant') }}</button>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold tracking-wide text-[color:var(--color-ink-soft)] uppercase">{{ __('admin.points_du_tenant') }}</div>
                    <a href="{{ route('admin.points.create', $this->tenant) }}" class="text-xs font-semibold text-[color:var(--color-ink)] underline">
                        + {{ __('admin.ajouter_point') }}
                    </a>
                </div>
                <div class="flex flex-col gap-3">
                    @foreach ($this->tenant->points as $point)
                        <div class="border border-[color:var(--color-line)] rounded-xl p-4">
                            <div class="font-semibold text-sm text-[color:var(--color-ink)]">{{ $point->nom }}</div>
                            @if ($point->localisation)
                                <div class="text-xs text-[color:var(--color-ink-soft)] mt-0.5">{{ $point->localisation }}</div>
                            @endif

                            <div class="flex items-center justify-between mt-3 mb-1.5">
                                <div class="text-[11px] font-semibold text-[color:var(--color-ink-soft)] uppercase">{{ __('admin.agents_du_point') }}</div>
                                <a href="{{ route('admin.agents.create', $point) }}" class="text-[11px] font-semibold text-[color:var(--color-ink)] underline">
                                    + {{ __('admin.ajouter_agent') }}
                                </a>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($point->agents as $agent)
                                    <span class="text-xs font-semibold px-2.5 py-1.5 rounded-md bg-[color:var(--color-sand-deep)] text-[color:var(--color-ink)]">
                                        {{ $agent->name }} ({{ $agent->telephone }})
                                    </span>
                                @empty
                                    <span class="text-xs text-[color:var(--color-ink-soft)]">—</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

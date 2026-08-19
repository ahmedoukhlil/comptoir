<div class="min-h-screen">
    <div class="mx-auto max-w-[600px] py-10 px-6">
        <div class="bg-[color:var(--color-paper)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-white px-6 py-5">
                <a href="{{ route('admin.tenants.show', $this->point->tenant) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-white/90 hover:text-white underline decoration-white/40 underline-offset-2"><span aria-hidden="true">&larr;</span> {{ __('admin.retour_au_tenant') }}</a>
                <div class="mt-1.5 font-[family-name:var(--font-heading)] font-bold text-lg">{{ __('admin.nouvel_agent') }}</div>
                <div class="text-xs text-[#9AA6C0] mt-0.5">{{ $this->point->nom }}</div>
            </div>

            <div class="p-6">
                @if ($motDePasseGenere)
                    <div class="bg-[color:var(--color-green)]/10 border border-[color:var(--color-green)]/30 rounded-xl p-4 mb-6">
                        <div class="text-sm font-semibold text-[color:var(--color-green-deep)]">{{ __('admin.agent_cree') }}</div>
                        <div class="text-xs text-[color:var(--color-ink-soft)] mt-2">{{ __('admin.mot_de_passe_genere') }}</div>
                        <div class="font-[family-name:var(--font-mono)] font-bold text-lg text-[color:var(--color-ink)] mt-0.5">{{ $motDePasseGenere }}</div>
                        <p class="text-xs text-[color:var(--color-ink-soft)] mt-2">{{ __('admin.mot_de_passe_note') }}</p>
                        <button type="button" wire:click="$set('motDePasseGenere', null)" class="text-xs font-semibold text-[color:var(--color-ink)] underline mt-3">
                            {{ __('admin.ajouter_un_autre_agent') }}
                        </button>
                    </div>
                @else
                    <form wire:submit="creer" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('admin.agent_nom_label') }}</label>
                            <input type="text" wire:model="nom" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                            @error('nom') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('admin.agent_telephone_label') }}</label>
                            <input type="tel" inputmode="numeric" dir="ltr" wire:model="telephone" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-sm font-[family-name:var(--font-mono)] outline-none focus:border-[color:var(--color-ink)]">
                            @error('telephone') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('admin.agent_role_label') }}</label>
                            <div class="flex gap-1.5">
                                @foreach (['agent', 'proprietaire'] as $r)
                                    <button
                                        type="button"
                                        wire:click="$set('role', '{{ $r }}')"
                                        class="text-xs font-semibold px-3 py-2 rounded-lg border-[1.5px] {{ $role === $r ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] text-[color:var(--color-ink-soft)]' }}"
                                    >{{ __('admin.role_'.$r) }}</button>
                                @endforeach
                            </div>
                        </div>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full rounded-xl bg-[color:var(--color-ink)] text-white font-semibold py-3 mt-2"
                        >{{ __('admin.creer') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

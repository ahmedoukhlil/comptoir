<div class="min-h-screen">
    <div class="mx-auto max-w-[600px] py-10 px-6">
        <div class="bg-[color:var(--color-paper)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-white px-6 py-5">
                <a href="{{ route('admin.tenants.show', $this->tenant) }}" class="text-[11px] font-semibold text-[#9AA6C0] underline">{{ __('admin.retour_au_tenant') }}</a>
                <div class="mt-1.5 font-[family-name:var(--font-heading)] font-bold text-lg">{{ __('admin.nouveau_point') }}</div>
                <div class="text-xs text-[#9AA6C0] mt-0.5">{{ $this->tenant->nom }}</div>
            </div>

            <div class="p-6">
                @if ($cree)
                    <div class="bg-[color:var(--color-green)]/10 border border-[color:var(--color-green)]/30 rounded-xl p-4 mb-4">
                        <div class="text-sm font-semibold text-[color:var(--color-green-deep)]">{{ __('admin.point_cree') }}</div>
                        <button type="button" wire:click="$set('cree', false)" class="text-xs font-semibold text-[color:var(--color-ink)] underline mt-2">
                            {{ __('admin.ajouter_un_autre_point') }}
                        </button>
                    </div>
                @endif

                <form wire:submit="creer" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('admin.point_nom_label') }}</label>
                        <input type="text" wire:model="nom" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                        @error('nom') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('admin.point_localisation_label') }}</label>
                        <input type="text" wire:model="localisation" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-[color:var(--color-ink)] text-white font-semibold py-3 mt-2"
                    >{{ __('admin.creer') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

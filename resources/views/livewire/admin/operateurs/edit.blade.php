<div class="min-h-screen">
    <div class="mx-auto max-w-[700px] py-10 px-6">
        <div class="bg-[color:var(--color-paper)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-white px-6 py-5">
                <span class="block font-[family-name:var(--font-heading)] font-bold text-base">{{ __('caisse.operateur_modifier_titre') }} — {{ $this->operateur->nom }}</span>
            </div>

            <div class="p-6">
                <form wire:submit="modifier" class="space-y-4">
                    @include('livewire.admin.operateurs._onglets-baremes')

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" wire:model="commissionVerseeDansSolde" class="mt-0.5 w-4 h-4 rounded border-[color:var(--color-line)] text-[color:var(--color-ink)]">
                        <span class="text-xs text-[color:var(--color-ink-soft)]">{{ __('caisse.operateur_versee_dans_solde_aide') }}</span>
                    </label>

                    <div class="flex gap-2 pt-2">
                        <a href="{{ route('admin.operateurs.index') }}" class="flex-1 text-center text-sm font-semibold py-3 rounded-xl text-[color:var(--color-ink-soft)] bg-[color:var(--color-sand)]">{{ __('caisse.annuler') }}</a>
                        <button type="submit" wire:loading.attr="disabled" class="flex-1 rounded-xl bg-[color:var(--color-ink)] text-white font-[family-name:var(--font-heading)] font-bold py-3">{{ __('caisse.enregistrer') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

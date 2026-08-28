<div class="min-h-screen">
    <div class="mx-auto max-w-[700px] py-10 px-6">
        <div class="bg-[color:var(--color-paper)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-white px-6 py-5">
                <span class="block font-[family-name:var(--font-heading)] font-bold text-base">{{ __('caisse.operateurs_nouveau') }}</span>
            </div>

            <div class="p-6">
                <form wire:submit="creer" class="space-y-4">
                    <div>
                        <label for="operateur-nom" class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.operateur_nom_label') }}</label>
                        <input id="operateur-nom" type="text" wire:model="nom" placeholder="{{ __('caisse.operateur_nom_placeholder') }}" class="w-full rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-sand)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                        @error('nom') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="operateur-logo" class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.operateur_logo_label') }}</label>
                        <div class="flex items-center gap-3">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" alt="" class="w-14 h-14 rounded-lg object-contain border border-[color:var(--color-line)] bg-[color:var(--color-sand)]">
                            @endif
                            <input id="operateur-logo" type="file" wire:model="logo" accept="image/*" class="flex-1 text-sm text-[color:var(--color-ink-soft)] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[color:var(--color-sand)] file:text-[color:var(--color-ink)]">
                        </div>
                        <p class="text-[11px] text-[color:var(--color-ink-soft)] mt-1">{{ __('caisse.operateur_logo_aide') }}</p>
                        @error('logo') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1.5">{{ __('caisse.operateur_type_label') }}</span>
                        <div class="flex gap-1.5" role="group" aria-label="{{ __('caisse.operateur_type_label') }}">
                            <button type="button" wire:click="$set('estCash', false)" aria-pressed="{{ $estCash ? 'false' : 'true' }}" class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] {{ ! $estCash ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-sand)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.operateur_type_mobile') }}</button>
                            <button type="button" wire:click="$set('estCash', true)" aria-pressed="{{ $estCash ? 'true' : 'false' }}" class="text-sm font-bold px-3.5 py-2.5 rounded-xl border-[1.5px] font-[family-name:var(--font-heading)] {{ $estCash ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-sand)] text-[color:var(--color-ink-soft)]' }}">{{ __('caisse.operateur_type_cash') }}</button>
                        </div>
                    </div>

                    @unless ($estCash)
                        @include('livewire.admin.operateurs._onglets-baremes')

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model="commissionVerseeDansSolde" class="mt-0.5 w-4 h-4 rounded border-[color:var(--color-line)] text-[color:var(--color-ink)]">
                            <span class="text-xs text-[color:var(--color-ink-soft)]">{{ __('caisse.operateur_versee_dans_solde_aide') }}</span>
                        </label>
                    @endunless

                    <div class="flex gap-2 pt-2">
                        <a href="{{ route('admin.operateurs.index') }}" class="flex-1 text-center text-sm font-semibold py-3 rounded-xl text-[color:var(--color-ink-soft)] bg-[color:var(--color-sand)]">{{ __('caisse.annuler') }}</a>
                        <button type="submit" wire:loading.attr="disabled" class="flex-1 rounded-xl bg-[color:var(--color-ink)] text-white font-[family-name:var(--font-heading)] font-bold py-3">{{ __('caisse.creer') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

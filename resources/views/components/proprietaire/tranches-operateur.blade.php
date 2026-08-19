@props(['tranches', 'prefix' => ''])

<fieldset>
    <legend class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1.5 text-start w-full">{{ __('caisse.operateur_tranches_label') }}</legend>
    <div class="flex flex-col gap-2">
        @foreach ($tranches as $index => $tranche)
            <div class="flex items-center gap-1.5" dir="ltr">
                <label class="sr-only" for="tranche-{{ $index }}-min">{{ __('caisse.operateur_tranche_min') }}</label>
                <input id="tranche-{{ $index }}-min" type="number" min="0" inputmode="numeric" wire:model="tranches.{{ $index }}.min" placeholder="{{ __('caisse.operateur_tranche_min') }}" class="w-0 flex-1 min-w-0 rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-2 py-2 text-xs font-[family-name:var(--font-mono)] outline-none focus:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                <span class="text-[color:var(--color-ink-soft)] text-xs" aria-hidden="true">–</span>
                <label class="sr-only" for="tranche-{{ $index }}-max">{{ __('caisse.operateur_tranche_max') }}</label>
                <input id="tranche-{{ $index }}-max" type="number" min="0" inputmode="numeric" wire:model="tranches.{{ $index }}.max" placeholder="{{ __('caisse.operateur_tranche_max') }}" class="w-0 flex-1 min-w-0 rounded-lg border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-2 py-2 text-xs font-[family-name:var(--font-mono)] outline-none focus:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                <label class="sr-only" for="tranche-{{ $index }}-frais">{{ __('caisse.operateur_tranche_frais') }}</label>
                <input id="tranche-{{ $index }}-frais" type="number" min="0" inputmode="numeric" wire:model="tranches.{{ $index }}.frais" placeholder="{{ __('caisse.operateur_tranche_frais') }}" class="w-0 flex-1 min-w-0 rounded-lg border-[1.5px] border-[color:var(--color-line)] bg-[color:var(--color-sand-deep)] px-2 py-2 text-xs font-[family-name:var(--font-mono)] font-bold outline-none focus:border-[color:var(--color-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
                @if (count($tranches) > 1)
                    <button
                        type="button"
                        wire:click="retirerTranche({{ $index }})"
                        aria-label="{{ __('caisse.operateur_retirer_tranche') }}"
                        class="flex items-center justify-center w-6 h-6 flex-shrink-0 text-[color:var(--color-rust-deep)] text-lg leading-none rounded hover:bg-[color:var(--color-rust)]/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-rust-deep)]"
                    >×</button>
                @endif
            </div>
        @endforeach
    </div>
    @error('tranches') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1.5">{{ $message }}</p> @enderror
    @error('tranches.*.min') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1.5">{{ $message }}</p> @enderror
    @error('tranches.*.max') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1.5">{{ $message }}</p> @enderror
    @error('tranches.*.frais') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1.5">{{ $message }}</p> @enderror

    <button type="button" wire:click="ajouterTranche" class="text-xs font-semibold text-[color:var(--color-ink)] underline mt-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--color-ink)]">
        + {{ __('caisse.operateur_ajouter_tranche') }}
    </button>
</fieldset>

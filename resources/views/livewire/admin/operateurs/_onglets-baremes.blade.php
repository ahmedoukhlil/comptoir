<div>
    <div class="flex gap-1.5 mb-3" role="tablist" aria-label="{{ __('caisse.operateur_onglets_bareme') }}">
        @foreach (['retrait', 'retrait_beneficiaire'] as $type)
            <button
                type="button"
                role="tab"
                aria-selected="{{ $onglet === $type ? 'true' : 'false' }}"
                wire:click="changerOnglet('{{ $type }}')"
                class="flex-1 text-xs font-semibold px-2 py-2 rounded-lg border-[1.5px] font-[family-name:var(--font-heading)] {{ $onglet === $type ? 'border-[color:var(--color-ink)] bg-[color:var(--color-ink)] text-white' : 'border-[color:var(--color-line)] bg-[color:var(--color-sand)] text-[color:var(--color-ink-soft)]' }}"
            >{{ __('caisse.'.$type) }}</button>
        @endforeach
    </div>

    @foreach (['depot', 'retrait', 'retrait_beneficiaire'] as $type)
        <div @if ($onglet !== $type) style="display:none" @endif class="space-y-4">
            <div class="flex items-center gap-2 flex-wrap bg-[color:var(--color-sand)] border-[1.5px] border-dashed border-[color:var(--color-line)] rounded-lg px-3 py-2.5">
                <label class="flex-1 min-w-[160px] text-xs font-semibold text-[color:var(--color-ink)] cursor-pointer">
                    <span class="block mb-1">{{ __('caisse.import_tranches_label') }}</span>
                    <input type="file" wire:model="fichierImport" accept=".xlsx,.xls" class="block w-full text-xs">
                </label>
                <button
                    type="button"
                    wire:click="importerTranches"
                    wire:loading.attr="disabled"
                    class="text-xs font-semibold px-3 py-2 rounded-lg bg-[color:var(--color-ink)] text-white flex-shrink-0"
                >{{ __('caisse.import_tranches_bouton') }}</button>
                <a href="{{ route('admin.operateurs.modele-import') }}" class="text-xs font-semibold text-[color:var(--color-ink-soft)] underline flex-shrink-0">
                    {{ __('caisse.import_tranches_modele') }}
                </a>
            </div>
            @error('fichierImport') <p class="text-xs text-[color:var(--color-rust-deep)]">{{ $message }}</p> @enderror

            <x-proprietaire.tranches-operateur :tranches="$tranchesParType[$type]" :type="$type" />

            <div>
                <label for="pourcentage-{{ $type }}" class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('caisse.operateur_partage_label') }}</label>
                <p class="text-[11px] text-[color:var(--color-ink-soft)] mb-1.5">{{ __('caisse.operateur_partage_aide') }}</p>
                <div class="flex items-center gap-2.5 bg-[color:var(--color-sand)] border-[1.5px] border-[color:var(--color-line)] rounded-lg px-3.5 py-2.5">
                    <input id="pourcentage-{{ $type }}" type="number" step="0.1" min="0" max="100" inputmode="decimal" dir="ltr" wire:model="pourcentagesParType.{{ $type }}" class="flex-1 border-none bg-transparent outline-none text-sm font-semibold text-[color:var(--color-ink)] font-[family-name:var(--font-mono)]">
                    <span class="text-sm text-[color:var(--color-ink-soft)]">%</span>
                </div>
                @error("pourcentagesParType.{$type}") <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    @endforeach
</div>

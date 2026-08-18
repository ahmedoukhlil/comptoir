@php
    $t = fn ($cle) => __(($estSuperAdmin ? 'admin.' : 'caisse.').$cle);
    $retourRoute = $estSuperAdmin
        ? route('admin.tenants.index')
        : (auth()->user()->peutUtiliserLaCaisse() ? route('caisse.saisie') : route('proprietaire.dashboard'));
@endphp
<div class="min-h-screen {{ $estSuperAdmin ? '' : 'bg-gradient-to-b from-[#0A2242] to-[color:var(--color-ink)] md:bg-none md:bg-[color:var(--color-sand)] '.(app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '') }}">
    <div class="mx-auto max-w-[500px] py-10 px-6">
        <div class="bg-[color:var(--color-paper)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="bg-[color:var(--color-ink)] text-white px-6 py-5">
                <a href="{{ $retourRoute }}" class="text-[11px] font-semibold text-[#9AA6C0] underline">{{ $t('retour') }}</a>
                <div class="mt-1.5 font-[family-name:var(--font-heading)] {{ $estSuperAdmin ? '' : 'rtl:font-[family-name:var(--font-arabic)]' }} font-bold text-lg">{{ $t('changer_mot_de_passe_titre') }}</div>
            </div>

            <div class="p-6">
                @if ($reussi)
                    <div class="bg-[color:var(--color-green)]/10 border border-[color:var(--color-green)]/30 rounded-xl p-4 mb-4">
                        <div class="text-sm font-semibold text-[color:var(--color-green-deep)]">{{ $t('mot_de_passe_change_avec_succes') }}</div>
                    </div>
                @endif

                <form wire:submit="changer" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ $t('mot_de_passe_actuel_label') }}</label>
                        <input type="password" wire:model="motDePasseActuel" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                        @error('motDePasseActuel') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ $t('nouveau_mot_de_passe_label') }}</label>
                        <input type="password" wire:model="nouveauMotDePasse" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                        @error('nouveauMotDePasse') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ $t('nouveau_mot_de_passe_confirmation_label') }}</label>
                        <input type="password" wire:model="nouveauMotDePasse_confirmation" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-sm outline-none focus:border-[color:var(--color-ink)]">
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-[color:var(--color-ink)] text-white font-semibold py-3 mt-2"
                    >{{ $t('changer_mot_de_passe_confirmer') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

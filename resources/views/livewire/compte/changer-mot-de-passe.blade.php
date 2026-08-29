@php
    $t = fn ($cle) => __(($estSuperAdmin ? 'admin.' : 'caisse.').$cle);
    $retourRoute = $estSuperAdmin
        ? route('admin.tenants.index')
        : (auth()->user()->peutUtiliserLaCaisse() ? route('caisse.saisie') : route('proprietaire.dashboard'));
@endphp
<main class="min-h-screen {{ $estSuperAdmin ? '' : 'bg-[color:var(--color-sand)] '.(app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '') }}">
    <div class="mx-auto max-w-[500px] py-10 px-6">
        <div class="bg-[color:var(--color-card)] rounded-2xl border border-[color:var(--color-line)] shadow-sm overflow-hidden">

            <div class="px-6 py-5 text-white" style="background: linear-gradient(155deg, var(--color-ink) 0%, var(--color-secondary) 100%);">
                <div class="flex items-center gap-3">
                    <x-bouton-retour :href="$retourRoute" :libelle="$t('retour')" />
                    <h1 class="font-[family-name:var(--font-heading)] {{ $estSuperAdmin ? '' : 'rtl:font-[family-name:var(--font-arabic)]' }} font-bold text-lg">{{ $t('changer_mot_de_passe_titre') }}</h1>
                </div>
            </div>

            <div class="p-6">
                @if ($reussi)
                    <div class="bg-[color:var(--color-green)]/10 border border-[color:var(--color-green)]/30 rounded-xl p-4 mb-4">
                        <div class="text-sm font-semibold text-[color:var(--color-green-deep)]">{{ $t('mot_de_passe_change_avec_succes') }}</div>
                    </div>
                @endif

                <form wire:submit="changer" class="space-y-4">
                    <div>
                        <label for="mot-de-passe-actuel" class="block text-sm font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ $t('mot_de_passe_actuel_label') }}</label>
                        <input id="mot-de-passe-actuel" type="password" wire:model="motDePasseActuel" autocomplete="current-password" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-base outline-none focus:border-[color:var(--color-ink)]">
                        @error('motDePasseActuel') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="nouveau-mot-de-passe" class="block text-sm font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ $t('nouveau_mot_de_passe_label') }}</label>
                        <input id="nouveau-mot-de-passe" type="password" wire:model="nouveauMotDePasse" autocomplete="new-password" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-base outline-none focus:border-[color:var(--color-ink)]">
                        @error('nouveauMotDePasse') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="confirmation-mot-de-passe" class="block text-sm font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ $t('nouveau_mot_de_passe_confirmation_label') }}</label>
                        <input id="confirmation-mot-de-passe" type="password" wire:model="nouveauMotDePasse_confirmation" autocomplete="new-password" class="w-full rounded-lg border border-[color:var(--color-line)] px-3.5 py-2.5 text-base outline-none focus:border-[color:var(--color-ink)]">
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
</main>

<main class="min-h-screen flex items-center justify-center px-4 relative" style="background: linear-gradient(180deg, #0A2242 0%, var(--color-ink) 100%);">
    <x-selecteur-langue class="absolute top-6 end-6" />

    <div class="w-full max-w-sm bg-[color:var(--color-sand)] rounded-3xl shadow-2xl p-8">
        <div class="text-center mb-6">
            <img src="/Logo_comptoirVF.png" alt="{{ __('caisse.app_nom') }}" class="w-48 h-48 mx-auto">
            <h1 class="sr-only">{{ __('auth.se_connecter') }} — {{ __('caisse.app_nom') }}</h1>
        </div>

        <form wire:submit="seConnecter" class="space-y-4">
            <div>
                <label for="connexion-telephone" class="block text-sm font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('auth.telephone_label') }}</label>
                <input
                    id="connexion-telephone"
                    type="tel"
                    wire:model="telephone"
                    inputmode="numeric"
                    autocomplete="tel"
                    autofocus
                    class="w-full rounded-xl border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-4 py-3 font-[family-name:var(--font-mono)] text-lg font-bold text-[color:var(--color-ink)] outline-none focus:border-[color:var(--color-ink)]"
                    placeholder="{{ __('auth.telephone_placeholder') }}"
                >
                @error('telephone') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1" role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="connexion-mot-de-passe" class="block text-sm font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('auth.mot_de_passe_label') }}</label>
                <input
                    id="connexion-mot-de-passe"
                    type="password"
                    wire:model="password"
                    autocomplete="current-password"
                    class="w-full rounded-xl border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-4 py-3 text-[color:var(--color-ink)] outline-none focus:border-[color:var(--color-ink)]"
                >
                @error('password') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1" role="alert">{{ $message }}</p> @enderror
            </div>

            @if ($error)
                <p class="text-sm text-[color:var(--color-rust-deep)] bg-[color:var(--color-rust-deep)]/10 rounded-lg px-3 py-2" role="alert">{{ $error }}</p>
            @endif

            <button
                type="submit"
                class="w-full rounded-xl bg-[color:var(--color-ink)] text-white font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold py-3.5 mt-2 flex items-center justify-center gap-2 disabled:opacity-70"
                wire:loading.attr="disabled"
            >
                <svg wire:loading wire:target="seConnecter" class="animate-spin h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                {{ __('auth.se_connecter') }}
            </button>
        </form>
    </div>
</main>

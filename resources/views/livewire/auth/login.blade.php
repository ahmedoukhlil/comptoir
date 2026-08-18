<div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-[#0A2242] to-[color:var(--color-ink)] px-4 relative">
    <x-selecteur-langue class="absolute top-6 end-6" />

    <div class="w-full max-w-sm bg-[color:var(--color-sand)] rounded-3xl shadow-2xl p-8">
        <div class="text-center mb-6">
            <img src="/comptoir_logo.png" alt="{{ __('caisse.app_nom') }}" class="w-48 h-48 mx-auto">
        </div>

        <form wire:submit="seConnecter" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('auth.telephone_label') }}</label>
                <input
                    type="tel"
                    wire:model="telephone"
                    inputmode="numeric"
                    autofocus
                    class="w-full rounded-xl border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-4 py-3 font-[family-name:var(--font-mono)] text-lg font-bold text-[color:var(--color-ink)] outline-none focus:border-[color:var(--color-ink)]"
                    placeholder="{{ __('auth.telephone_placeholder') }}"
                >
                @error('telephone') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-[color:var(--color-ink-soft)] mb-1">{{ __('auth.mot_de_passe_label') }}</label>
                <input
                    type="password"
                    wire:model="password"
                    class="w-full rounded-xl border border-[color:var(--color-line)] bg-[color:var(--color-paper)] px-4 py-3 text-[color:var(--color-ink)] outline-none focus:border-[color:var(--color-ink)]"
                >
                @error('password') <p class="text-xs text-[color:var(--color-rust-deep)] mt-1">{{ $message }}</p> @enderror
            </div>

            @if ($error)
                <p class="text-sm text-[color:var(--color-rust-deep)] bg-[color:var(--color-rust-deep)]/10 rounded-lg px-3 py-2">{{ $error }}</p>
            @endif

            <button
                type="submit"
                class="w-full rounded-xl bg-[color:var(--color-ink)] text-white font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)] font-bold py-3.5 mt-2"
                wire:loading.attr="disabled"
            >
                {{ __('auth.se_connecter') }}
            </button>
        </form>
    </div>
</div>

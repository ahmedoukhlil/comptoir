<div
    id="bandeau-installer-pwa"
    style="display:none;position:fixed;inset-inline:0;bottom:0;z-index:9998;"
    class="px-4 pb-4"
>
    <div class="mx-auto max-w-[420px] bg-[color:var(--color-ink)] text-white rounded-2xl shadow-xl px-4 py-3.5 flex items-center gap-3">
        <img src="/icon-192.png" alt="" width="36" height="36" class="rounded-lg flex-shrink-0">
        <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold font-[family-name:var(--font-heading)] rtl:font-[family-name:var(--font-arabic)]">{{ __('caisse.pwa_installer_titre') }}</div>
            <div class="text-[11px] text-white/75">{{ __('caisse.pwa_installer_texte') }}</div>
        </div>
        <button
            type="button"
            id="bandeau-installer-pwa-bouton"
            class="text-xs font-bold px-3.5 py-2 rounded-lg bg-white text-[color:var(--color-ink)] flex-shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
        >{{ __('caisse.pwa_installer_bouton') }}</button>
        <button
            type="button"
            id="bandeau-installer-pwa-fermer"
            aria-label="{{ __('caisse.annuler') }}"
            class="text-white/60 flex-shrink-0 p-1 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
</div>
<script>
    (function () {
        var CLE_MASQUE = 'comptoir-pwa-installer-masque';
        var evenementDiffere = null;
        var bandeau = document.getElementById('bandeau-installer-pwa');

        window.addEventListener('beforeinstallprompt', function (e) {
            if (localStorage.getItem(CLE_MASQUE) === '1') return;

            e.preventDefault();
            evenementDiffere = e;
            bandeau.style.display = 'block';
        });

        document.getElementById('bandeau-installer-pwa-bouton').addEventListener('click', function () {
            if (! evenementDiffere) return;

            evenementDiffere.prompt();
            evenementDiffere.userChoice.finally(function () {
                evenementDiffere = null;
                bandeau.style.display = 'none';
            });
        });

        document.getElementById('bandeau-installer-pwa-fermer').addEventListener('click', function () {
            localStorage.setItem(CLE_MASQUE, '1');
            bandeau.style.display = 'none';
        });

        window.addEventListener('appinstalled', function () {
            bandeau.style.display = 'none';
        });
    })();
</script>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Le Comptoir / القنطوار</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <meta name="theme-color" content="#123A66">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-[color:var(--color-sand)] {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <x-bandeau-essai />
    {{ $slot }}
    @livewireScripts
    <script>
        window.ComptoirTraductions = {
            erreurTelephoneDigits: @json(__('caisse.erreur_telephone_digits')),
            erreurMontantVide: @json(__('caisse.erreur_montant_vide')),
            syncEnAttente: @json(__('caisse.sync_en_attente')),
            syncBadgeAttente: @json(__('caisse.sync_badge_attente')),
            clotureEcartAucun: @json(__('caisse.cloture_ecart_aucun')),
            clotureEcartPositif: @json(__('caisse.cloture_ecart_positif')),
            clotureEcartNegatif: @json(__('caisse.cloture_ecart_negatif')),
        };

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
        document.addEventListener('livewire:init', () => {
            Livewire.on('langue-changee', ({ locale }) => {
                document.documentElement.setAttribute('lang', locale);
                document.documentElement.setAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr');
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Le Comptoir / القنطوار</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="apple-touch-icon" href="/icon-192.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased {{ app()->getLocale() === 'ar' ? 'font-[family-name:var(--font-arabic)]' : '' }}">
    <x-splash-pwa />

    {{ $slot }}
    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('langue-changee', ({ locale }) => {
                document.documentElement.setAttribute('lang', locale);
                document.documentElement.setAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr');
            });
        });
    </script>
</body>
</html>

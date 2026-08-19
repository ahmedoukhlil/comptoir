<div
    id="splash-pwa"
    style="position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;background:linear-gradient(155deg, #123A66 0%, #4A7FB5 100%);"
>
    <img src="/icon-192.png" alt="" style="width:132px;height:132px;object-fit:contain;border-radius:28px;box-shadow:0 12px 40px rgba(0,0,0,0.25);">
</div>
<script>
    (function () {
        var estStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (! estStandalone) return;

        var splash = document.getElementById('splash-pwa');
        splash.style.display = 'flex';

        window.addEventListener('load', function () {
            setTimeout(function () {
                splash.style.transition = 'opacity 0.35s ease';
                splash.style.opacity = '0';
                setTimeout(function () { splash.remove(); }, 400);
            }, 400);
        });
    })();
</script>

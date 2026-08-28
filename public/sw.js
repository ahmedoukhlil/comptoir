const CACHE_VERSION = 'comptoir-v13';
const CACHE_SHELL = `${CACHE_VERSION}-shell`;
const CACHE_RUNTIME = `${CACHE_VERSION}-runtime`;

const URLS_APP_SHELL = [
    '/caisse',
    '/manifest.json',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_SHELL).then((cache) => cache.addAll(URLS_APP_SHELL)).catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((clefs) => Promise.all(
            clefs
                .filter((clef) => ! clef.startsWith(CACHE_VERSION))
                .map((clef) => caches.delete(clef))
        ))
    );
    self.clients.claim();
});

function estRequeteAsset(url) {
    return url.pathname.startsWith('/build/') || url.pathname === '/manifest.json' || /\.(woff2?|png|ico)$/.test(url.pathname);
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Assets statiques (CSS/JS/polices/icônes) : cache d'abord, réseau en secours.
    if (estRequeteAsset(url)) {
        event.respondWith(
            caches.match(request).then((reponseCache) => {
                if (reponseCache) {
                    return reponseCache;
                }

                return fetch(request).then((reponseReseau) => {
                    const copie = reponseReseau.clone();
                    caches.open(CACHE_RUNTIME).then((cache) => cache.put(request, copie));

                    return reponseReseau;
                });
            })
        );

        return;
    }

    // Navigation (pages HTML) : réseau d'abord, cache en secours si hors-ligne.
    if (request.mode === 'navigate' || url.pathname === '/caisse') {
        event.respondWith(
            fetch(request)
                .then((reponseReseau) => {
                    const copie = reponseReseau.clone();
                    caches.open(CACHE_SHELL).then((cache) => cache.put(request, copie));

                    return reponseReseau;
                })
                .catch(() => caches.match(request).then((r) => r || caches.match('/caisse')))
        );
    }
});

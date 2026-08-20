import comptoirSaisie from './comptoir-saisie.js';
import { demarrerSynchronisationAutomatique } from './offline-sync.js';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('comptoirSaisie', comptoirSaisie);
});

demarrerSynchronisationAutomatique();

// Sur mobile, l'apparition du clavier virtuel redimensionne brusquement la
// viewport et peut laisser le champ actif caché ou mal positionné. On
// recentre systématiquement le champ après un court délai, le temps que le
// clavier finisse son animation, pour un défilement plus fluide sur toutes
// les pages plutôt que de le câbler champ par champ.
document.addEventListener('focusin', (e) => {
    if (! e.target.matches('input, textarea, select')) {
        return;
    }

    setTimeout(() => {
        e.target.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }, 200);
});

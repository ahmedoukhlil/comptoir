import comptoirSaisie from './comptoir-saisie.js';
import { demarrerSynchronisationAutomatique } from './offline-sync.js';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('comptoirSaisie', comptoirSaisie);
});

demarrerSynchronisationAutomatique();

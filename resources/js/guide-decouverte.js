// Coach marks minimalistes : une courte série d'étapes qui pointent vers des
// éléments réels de l'écran déjà chargé (pas un tutoriel séparé). Chaque
// étape est { cible: '#selecteur', texte: '...' }. `onTermine` est appelé une
// fois à la fin ou au clic sur "Passer", pour marquer le guide comme vu côté
// serveur — le composant ne connaît rien de Livewire lui-même.
export default function guideDecouverte({ etapes, onTermine, groupe = 'defaut', visibleInitial = true }) {
    return {
        etapes,
        groupe,
        index: 0,
        visible: etapes.length > 0 && visibleInitial,
        // Tant que positionner() n'a pas calcule une position reelle, la
        // carte reste cachee : sinon elle s'affiche brievement en haut a
        // gauche (top/left par defaut) et intercepte les touches/le scroll
        // a cet endroit avant d'atteindre sa position finale.
        positionCalculee: false,
        position: { top: 0, left: 0, placement: 'bas' },
        cibleActuelle: null,

        get etapeActuelle() {
            return this.etapes[this.index];
        },

        get derniereEtape() {
            return this.index === this.etapes.length - 1;
        },

        init() {
            // Debounce : sur iOS Safari, scrollIntoView() fait apparaitre/
            // disparaitre la barre d'adresse, ce qui declenche resize, qui
            // relancerait positionner() -> nouveau scrollIntoView() -> boucle
            // infinie sans jamais stabiliser la position (carte jamais
            // affichee, defilement bloque tant que ca tourne).
            let minuteurResize = null;
            window.addEventListener('resize', () => {
                clearTimeout(minuteurResize);
                minuteurResize = setTimeout(() => this.positionner(), 300);
            });
            window.addEventListener('guide:relancer', (e) => {
                if (e.detail?.groupe !== this.groupe) {
                    return;
                }

                this.index = 0;
                this.visible = true;
                this.$nextTick(() => this.positionner());
            });

            if (this.visible) {
                this.$nextTick(() => this.positionner());
            }
        },

        retirerSurbrillance() {
            this.cibleActuelle?.classList.remove('guide-cible-surlignee');
            this.cibleActuelle = null;
        },

        positionner() {
            this.retirerSurbrillance();
            this.positionCalculee = false;

            const cible = document.querySelector(this.etapeActuelle?.cible);

            if (! cible) {
                // Cible introuvable (écran encore en train de se rendre, ou
                // élément conditionnel absent) : on referme silencieusement
                // plutôt que d'afficher une carte flottante dans le vide.
                this.fermer();

                return;
            }

            // On ne scrolle que si la cible n'est pas deja raisonnablement
            // visible : resister a re-scroller inutilement evite de
            // redeclencher resize (barre d'adresse iOS) en boucle avec le
            // debounce ci-dessus.
            const rectAvant = cible.getBoundingClientRect();
            const dejaVisible = rectAvant.top >= 0 && rectAvant.bottom <= window.innerHeight;

            if (! dejaVisible) {
                cible.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }

            setTimeout(() => {
                const rect = cible.getBoundingClientRect();
                // Sur mobile, une barre fixe peut couvrir le bas de l'écran
                // (ex. barre résumé sur l'écran de saisie) : on l'exclut de
                // l'espace disponible pour ne pas placer la carte dessous.
                const barreBasse = document.querySelector('[data-guide-barre-basse]');
                const barreBasseFixe = barreBasse && getComputedStyle(barreBasse).position === 'fixed';
                const limiteBasse = barreBasseFixe
                    ? barreBasse.getBoundingClientRect().top
                    : window.innerHeight;
                const espaceEnBas = limiteBasse - rect.bottom;
                const placementBas = espaceEnBas > 160;
                const demiLargeurCarte = Math.min(140, window.innerWidth / 2 - 12);

                this.position = {
                    top: placementBas ? rect.bottom + 12 : rect.top - 12,
                    left: Math.min(
                        Math.max(rect.left + rect.width / 2, demiLargeurCarte + 12),
                        window.innerWidth - demiLargeurCarte - 12
                    ),
                    placement: placementBas ? 'bas' : 'haut',
                };
                this.positionCalculee = true;

                cible.classList.add('guide-cible-surlignee');
                this.cibleActuelle = cible;
            }, 250);
        },

        suivant() {
            if (this.derniereEtape) {
                this.fermer();

                return;
            }

            this.index++;
            this.$nextTick(() => this.positionner());
        },

        fermer() {
            this.retirerSurbrillance();
            this.visible = false;
            onTermine?.();
        },
    };
}

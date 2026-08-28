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
            // Le resize (barre d'adresse iOS qui apparait/disparait au
            // scroll, rotation d'ecran) ne doit que recalculer les
            // coordonnees de la carte deja affichee — jamais re-scroller
            // ni toucher au highlight, sinon un scroll manuel de
            // l'utilisateur declenche resize -> nouveau scrollIntoView()
            // qui le ramene de force vers la cible du guide.
            let minuteurResize = null;
            window.addEventListener('resize', () => {
                clearTimeout(minuteurResize);
                minuteurResize = setTimeout(() => this.recalculerPosition(), 300);
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

        /**
         * Recalcule uniquement les coordonnees de la carte pour la cible
         * courante, sans scroller ni re-toucher au highlight. Appele au
         * resize pour suivre un changement de mise en page.
         */
        recalculerPosition() {
            if (! this.visible || ! this.cibleActuelle) {
                return;
            }

            const rect = this.cibleActuelle.getBoundingClientRect();
            this.position = this.calculerCoordonnees(rect);
        },

        calculerCoordonnees(rect) {
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

            return {
                top: placementBas ? rect.bottom + 12 : rect.top - 12,
                left: Math.min(
                    Math.max(rect.left + rect.width / 2, demiLargeurCarte + 12),
                    window.innerWidth - demiLargeurCarte - 12
                ),
                placement: placementBas ? 'bas' : 'haut',
            };
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

            cible.scrollIntoView({ block: 'center', behavior: 'smooth' });

            setTimeout(() => {
                this.position = this.calculerCoordonnees(cible.getBoundingClientRect());
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

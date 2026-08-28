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
        position: { top: 0, left: 0, placement: 'bas' },
        cibleActuelle: null,

        get etapeActuelle() {
            return this.etapes[this.index];
        },

        get derniereEtape() {
            return this.index === this.etapes.length - 1;
        },

        init() {
            window.addEventListener('resize', () => this.positionner());
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

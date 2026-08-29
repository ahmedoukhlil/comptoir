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
                // Lancement automatique (premier login) : on attend que les
                // polices soient chargees pour eviter un calcul de position
                // base sur une mise en page qui va encore bouger (texte en
                // police de secours plus etroite/large que la police finale).
                // document.fonts.ready est deja resolue si les polices sont
                // chargees depuis longtemps, donc pas de delai superflu sur
                // les lancements suivants ni en navigation interne.
                const pret = window.document.fonts?.ready ?? Promise.resolve();

                pret.then(() => this.$nextTick(() => this.positionner()));
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
            // document.documentElement.clientWidth (contrairement a
            // window.innerWidth sur certains navigateurs) exclut la largeur
            // de la barre de defilement verticale : sur l'ecran de saisie,
            // souvent plus haut que la fenetre, s'appuyer sur innerWidth
            // faisait deborder la carte au-dela du bord reellement visible.
            const largeurDisponible = document.documentElement.clientWidth;

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

            // Largeur reelle de la carte (voir w-[280px] dans le composant
            // Blade), reduite sur les tres petits ecrans. Le calcul se fait
            // ici une seule fois : `left` est directement le bord gauche
            // final de la carte, pas un point central a retraiter cote Blade
            // (l'ancienne duplication du calcul dans le style inline pouvait
            // diverger d'un pixel et laisser deborder le bouton "Suivant").
            const largeurCarte = Math.min(280, largeurDisponible - 24);
            const centreCible = rect.left + rect.width / 2;

            return {
                top: placementBas ? rect.bottom + 12 : rect.top - 12,
                left: Math.min(
                    Math.max(centreCible - largeurCarte / 2, 12),
                    largeurDisponible - largeurCarte - 12
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

            this.attendreFinDuScroll(cible, () => {
                this.position = this.calculerCoordonnees(cible.getBoundingClientRect());
                this.positionCalculee = true;

                cible.classList.add('guide-cible-surlignee');
                this.cibleActuelle = cible;
            });
        },

        /**
         * scrollIntoView({ behavior: 'smooth' }) n'a pas de callback natif.
         * On attend que la position de la cible se stabilise entre deux
         * frames consecutives (au lieu d'un delai fixe arbitraire, trop
         * court sur un premier chargement a froid — polices/mise en page
         * pas encore stabilisees — et inutilement long sur un appareil
         * rapide). Plafonne a ~1.5s pour ne jamais rester bloque.
         */
        attendreFinDuScroll(cible, callback) {
            let dernierTop = null;
            let tentatives = 0;
            const maxTentatives = 90; // ~1.5s a 60fps

            const verifier = () => {
                const top = cible.getBoundingClientRect().top;
                tentatives++;

                if (top === dernierTop || tentatives >= maxTentatives) {
                    callback();

                    return;
                }

                dernierTop = top;
                requestAnimationFrame(verifier);
            };

            requestAnimationFrame(verifier);
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

import { ajouterOperationLocale, listerOperationsEnAttente, compterEnAttente } from './offline-db.js';

function calculerCommission(operateur, montant) {
    const tranches = operateur?.bareme_commission?.tranches ?? [];

    for (const tranche of tranches) {
        const min = tranche.min ?? 0;
        const max = tranche.max ?? null;

        if (montant >= min && (max === null || montant <= max)) {
            if (tranche.pourcentage !== undefined) {
                return Math.round(montant * tranche.pourcentage / 100);
            }

            return tranche.montant_fixe ?? 0;
        }
    }

    return 0;
}

export default function comptoirSaisie({ point, operateurs, soldesServeur, soldeTotalServeur }) {
    return {
        point,
        operateurs,
        soldesServeur, // { [operateur_id]: solde }
        soldeTotalServeur,
        enLigne: navigator.onLine,
        enAttente: 0,
        operationsLocalesAffichage: [],
        erreurLocale: '',
        enConfirmation: false,
        confirmationOuverte: false,

        type: 'depot',
        operateurId: operateurs[0]?.id ?? null,
        telephone: '',
        optionnelOuvert: false,
        clientNom: '',
        clientNni: '',
        montant: '',

        soldeOperateur(operateurId) {
            const impactLocal = this.operationsLocalesAffichage
                .filter(op => op.operateur_id === operateurId)
                .reduce((somme, op) => somme + (op.type === 'depot' ? op.montant : -op.montant), 0);

            return (this.soldesServeur[operateurId] ?? 0) + impactLocal;
        },

        get soldeAffiche() {
            const impactLocal = this.operationsLocalesAffichage.reduce(
                (somme, op) => somme + (op.type === 'depot' ? op.montant : -op.montant),
                0
            );

            return this.soldeTotalServeur + impactLocal;
        },

        get commissionActuelle() {
            const montant = parseInt(this.montant || '0', 10);

            if (montant <= 0 || ! this.operateurId) {
                return 0;
            }

            const operateur = this.operateurs.find(o => o.id === this.operateurId);

            return operateur ? calculerCommission(operateur, montant) : 0;
        },

        init() {
            window.addEventListener('online', () => { this.enLigne = true; });
            window.addEventListener('offline', () => { this.enLigne = false; });

            window.addEventListener('comptoir:sync-statut', (e) => {
                this.enAttente = e.detail.enAttente;
            });

            window.addEventListener('comptoir:sync-terminee', () => {
                this.chargerOperationsLocales();
                this.$wire.rafraichirApresSynchronisation().then((resultat) => {
                    if (resultat?.soldes) {
                        this.soldesServeur = resultat.soldes;
                        this.soldeTotalServeur = resultat.soldeTotal;
                    }
                });
            });

            this.chargerOperationsLocales();
            compterEnAttente().then(n => { this.enAttente = n; });
        },

        async chargerOperationsLocales() {
            const enAttente = await listerOperationsEnAttente();

            this.operationsLocalesAffichage = enAttente
                .slice()
                .reverse()
                .map(op => ({
                    ...op,
                    operateur_nom: this.operateurs.find(o => o.id === op.operateur_id)?.nom ?? '',
                    heure: new Date(op.cree_le).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
                }));
        },

        formaterMontant(valeur) {
            return Number(valeur || 0).toLocaleString('fr-FR').replace(/,/g, ' ');
        },

        texteSyncEnAttente(n) {
            return `${window.ComptoirTraductions?.syncEnAttente ?? ''} (${n})`;
        },

        taper(chiffre) {
            if (this.montant.length >= 9) {
                return;
            }

            this.montant = String(parseInt((this.montant || '0') + chiffre, 10));
        },

        effacer() {
            this.montant = '';
        },

        reinitialiserFormulaire() {
            this.telephone = '';
            this.clientNom = '';
            this.clientNni = '';
            this.montant = '';
            this.optionnelOuvert = false;
        },

        validerChamps() {
            const t = window.ComptoirTraductions ?? {};

            if (! /^\d{8}$/.test(this.telephone || '')) {
                return t.erreurTelephoneDigits ?? 'Numéro invalide.';
            }

            const montant = parseInt(this.montant || '0', 10);

            if (montant <= 0) {
                return t.erreurMontantVide ?? 'Entrez un montant.';
            }

            if (this.type === 'retrait' && montant > this.soldeOperateur(this.operateurId)) {
                return t.erreurSoldeInsuffisant ?? 'Solde insuffisant.';
            }

            return null;
        },

        ouvrirConfirmation() {
            this.erreurLocale = '';

            const erreur = this.validerChamps();

            if (erreur) {
                this.erreurLocale = erreur;

                return;
            }

            this.confirmationOuverte = true;
        },

        fermerConfirmation() {
            this.confirmationOuverte = false;
        },

        async confirmer() {
            this.erreurLocale = '';

            const erreur = this.validerChamps();

            if (erreur) {
                this.erreurLocale = erreur;
                this.confirmationOuverte = false;

                return;
            }

            this.enConfirmation = true;

            try {
                if (this.enLigne) {
                    const resultat = await this.$wire.confirmer({
                        type: this.type,
                        operateurId: this.operateurId,
                        telephone: this.telephone,
                        clientNom: this.clientNom,
                        clientNni: this.clientNni,
                        montant: this.montant,
                    });

                    if (resultat?.erreur) {
                        this.erreurLocale = resultat.erreur;

                        return;
                    }

                    if (resultat?.soldes) {
                        this.soldesServeur = resultat.soldes;
                        this.soldeTotalServeur = resultat.soldeTotal;
                    }

                    this.reinitialiserFormulaire();

                    return;
                }

                const operateur = this.operateurs.find(o => o.id === this.operateurId);
                const montant = parseInt(this.montant, 10);

                const operation = {
                    uuid_client: crypto.randomUUID(),
                    point_id: this.point.id,
                    operateur_id: this.operateurId,
                    type: this.type,
                    montant,
                    commission_calculee: calculerCommission(operateur, montant),
                    client_nom: this.clientNom || null,
                    client_telephone: this.telephone,
                    client_nni: this.clientNni || null,
                    cree_le: new Date().toISOString(),
                };

                await ajouterOperationLocale(operation);
                await this.chargerOperationsLocales();
                this.enAttente = await compterEnAttente();
                this.reinitialiserFormulaire();
            } finally {
                this.enConfirmation = false;
                this.confirmationOuverte = false;
            }
        },
    };
}

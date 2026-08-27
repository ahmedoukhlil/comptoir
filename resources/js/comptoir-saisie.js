import { ajouterOperationLocale, listerOperationsEnAttente, compterEnAttente } from './offline-db.js';

// Nom de l'attribut bareme_* correspondant à un type d'opération, en
// miroir exact de Operateur::attributBareme() côté PHP.
function attributBareme(typeOperation) {
    return {
        depot: 'bareme_depot',
        retrait: 'bareme_retrait_client',
        retrait_beneficiaire: 'bareme_retrait_beneficiaire',
    }[typeOperation];
}

// Nom de l'attribut pourcentage_partage_point_* correspondant à un type
// d'opération, en miroir exact de Operateur::attributPourcentagePartagePoint().
function attributPourcentagePartagePoint(typeOperation) {
    return {
        depot: 'pourcentage_partage_point_depot',
        retrait: 'pourcentage_partage_point_retrait_client',
        retrait_beneficiaire: 'pourcentage_partage_point_retrait_beneficiaire',
    }[typeOperation];
}

function calculerFrais(operateur, montant, typeOperation) {
    const bareme = operateur?.[attributBareme(typeOperation)];
    const tranches = bareme?.tranches ?? [];

    for (const tranche of tranches) {
        const min = tranche.min ?? 0;
        const max = tranche.max ?? null;

        if (montant >= min && (max === null || montant <= max)) {
            return tranche.frais ?? 0;
        }
    }

    return 0;
}

// Répartit le frais fixe entre part point de vente et part banque selon le
// pourcentage_partage_point_* propre à l'opérateur et au type d'opération —
// jamais un pourcentage codé en dur ici, en miroir exact de
// Operateur::repartirCommission().
function repartirCommission(operateur, montant, typeOperation) {
    const frais = calculerFrais(operateur, montant, typeOperation);
    const pourcentagePartagePoint = operateur?.[attributPourcentagePartagePoint(typeOperation)] ?? 0;
    const partPoint = Math.round(frais * pourcentagePartagePoint / 100);

    return { frais, partPoint, partBanque: frais - partPoint };
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

        // Aucune présélection par défaut : l'agent doit choisir explicitement
        // le type d'opération et l'opérateur à chaque saisie, pour éviter
        // les erreurs par omission (validerChamps() bloque tant que ces
        // deux champs restent vides).
        type: null,
        operateurId: null,
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

        get formulaireValide() {
            return this.validerChamps() === null;
        },

        get commissionActuelle() {
            const montant = parseInt(this.montant || '0', 10);

            if (montant <= 0 || ! this.operateurId) {
                return 0;
            }

            const operateur = this.operateurs.find(o => o.id === this.operateurId);

            return operateur ? repartirCommission(operateur, montant, this.type).partPoint : 0;
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

        libelleType(type) {
            const t = window.ComptoirTraductions ?? {};
            const libelles = {
                depot: t.libelleDepot,
                retrait: t.libelleRetrait,
                retrait_beneficiaire: t.libelleRetraitBeneficiaire,
            };

            return libelles[type] ?? type;
        },

        texteSyncEnAttente(n) {
            const arabe = document.documentElement.lang === 'ar';
            const texte = window.ComptoirTraductions?.syncEnAttente ?? (arabe ? 'بانتظار الإرسال' : "En attente d'envoi");

            return `${texte} (${n})`;
        },

        reinitialiserFormulaire() {
            this.type = null;
            this.operateurId = null;
            this.telephone = '';
            this.clientNom = '';
            this.clientNni = '';
            this.montant = '';
            this.optionnelOuvert = false;
        },

        validerChamps() {
            const t = window.ComptoirTraductions ?? {};
            const arabe = document.documentElement.lang === 'ar';

            if (! this.type) {
                return t.erreurTypeVide ?? (arabe ? 'اختر نوع العملية.' : "Choisissez le type d'opération.");
            }

            if (! this.operateurId) {
                return t.erreurOperateurVide ?? (arabe ? 'اختر المشغل.' : "Choisissez l'opérateur.");
            }

            if (! /^\d{8}$/.test(this.telephone || '')) {
                return t.erreurTelephoneDigits ?? (arabe ? 'رقم غير صحيح.' : 'Numéro invalide.');
            }

            const montant = parseInt(this.montant || '0', 10);

            if (montant <= 0) {
                return t.erreurMontantVide ?? (arabe ? 'أدخل مبلغاً.' : 'Entrez un montant.');
            }

            if ((this.type === 'retrait' || this.type === 'retrait_beneficiaire') && montant > this.soldeOperateur(this.operateurId)) {
                return t.erreurSoldeInsuffisant ?? (arabe ? 'الرصيد غير كافٍ.' : 'Solde insuffisant.');
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
                const repartition = repartirCommission(operateur, montant, this.type);

                const operation = {
                    uuid_client: crypto.randomUUID(),
                    point_id: this.point.id,
                    operateur_id: this.operateurId,
                    type: this.type,
                    montant,
                    commission_calculee: repartition.frais,
                    commission_part_point: repartition.partPoint,
                    commission_part_banque: repartition.partBanque,
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

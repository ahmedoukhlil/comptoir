# Prompt de développement — Le Comptoir / القنطوار
### À utiliser avec Claude Code pour générer le projet sprint par sprint

---

## Comment utiliser ce document

Copier/coller un sprint à la fois dans Claude Code, dans l'ordre. Ne pas tout donner d'un coup : chaque sprint doit être terminé, testé et validé avant de passer au suivant. Le bloc "Contexte global" doit être fourni une seule fois au début de la session (ou conservé dans un fichier `CLAUDE.md` à la racine du projet pour qu'il soit repris automatiquement à chaque session).

---

## Contexte global (à donner une seule fois, ou à mettre dans CLAUDE.md)

```
Tu m'aides à développer Le Comptoir (القنطوار en arabe — c'est le nom officiel
bilingue de l'app, à utiliser tel quel dans l'interface, jamais traduit
autrement), une application SaaS de gestion de caisse
pour les points de service mobile money en Mauritanie (Bankily, Masrivi, Sedad).

PROBLÈME RÉSOLU
Les agents de ces points enregistrent aujourd'hui leurs dépôts/retraits sur Excel
ou papier. Résultat : erreurs de calcul, aucune visibilité temps réel pour le
propriétaire qui gère plusieurs points, aucun historique fiable en cas de litige.

UTILISATEURS
1. Agent de point : saisit vite un dépôt/retrait, connaît son solde de caisse
   à tout instant. Souvent non technophile — l'app doit être utilisable sans
   formation.
2. Propriétaire / gérant multi-points : supervise plusieurs agents, alimente
   leur caisse en capital, détecte les écarts, consulte les bénéfices.

STACK TECHNIQUE (cohérente avec mes autres projets Syslog)
- Backend : Laravel (dernière version stable)
- Frontend : Livewire + Alpine.js, PWA avec Service Worker
- Base de données : MySQL
- Multi-tenant : package stancl/tenancy
- CSS : Tailwind CSS

EXIGENCES NON-FONCTIONNELLES QUI PRIMENT SUR TOUT LE RESTE
1. Simplicité pour non-initiés — priorité n°1, avant toute fonctionnalité avancée :
   - Vocabulaire du quotidien, pas de jargon comptable
     (« J'ai reçu de l'argent » plutôt que « Dépôt »)
   - Gros boutons, gros chiffres, un seul écran pour l'action principale
   - Saisie en 3 taps maximum pour une opération courante
   - Pas de formulaire classique : clavier numérique + choix par icônes/couleurs
2. Bilingue Français / Arabe avec bascule instantanée et RTL complet
   (mise en page miroir, pas juste la traduction du texte)
3. Hors-ligne d'abord : la synchronisation réseau n'est jamais bloquante,
   les opérations se mettent en file d'attente et se synchronisent dès que
   la connexion revient
4. Chaque opération est horodatée et non modifiable après la clôture de caisse
   (piste d'audit)
5. Le NNI du client est une donnée sensible : à chiffrer au repos, jamais
   exportée en clair dans un rapport non protégé

CONVENTION VISUELLE À RESPECTER
Dans toute vue listant des opérations : vert pour une entrée (dépôt),
rouge pour une sortie (retrait) — c'est la convention qu'utilisent déjà les
agents sur leurs feuilles Excel, ne pas s'en écarter.

Je vais te donner le développement sprint par sprint. Confirme que tu as bien
compris ce contexte avant de commencer le Sprint 1, et pose-moi des questions
si un point n'est pas clair plutôt que de faire des suppositions.
```

---

## Modèle de données de référence (valable pour tous les sprints)

```
Tenant (propriétaire)
  - id, nom, plan (solo | reseau | entreprise), statut, date_creation

Point
  - id, tenant_id, nom, localisation

Agent (utilisateur rattaché à un point)
  - id, tenant_id, point_id, nom, telephone, mot_de_passe, role (agent | proprietaire)

Operateur (référentiel : Bankily, Masrivi, Sedad)
  - id, nom, bareme_commission (JSON : tranches de montant → % ou montant fixe)

Operation
  - id, numero_piece (auto-généré, ex: OP-2026-000123)
  - point_id, agent_id, operateur_id
  - type (depot | retrait)
  - montant
  - commission_calculee
  - client_nom (nullable)
  - client_telephone (obligatoire)
  - client_nni (nullable, chiffré)
  - observation (nullable)
  - synced (booléen — pour la logique hors-ligne)
  - created_at

Alimentation (capital injecté par le propriétaire dans un point)
  - id, point_id, tenant_id, montant, date, note (nullable)

Cloture
  - id, point_id, agent_id, date
  - solde_theorique, solde_compte, ecart
```

Le solde de caisse d'un point à un instant T = somme des alimentations − somme des retraits + somme des dépôts, jusqu'à T.

---

## Sprint 1 — Fondations du projet

```
Sprint 1 : Fondations

1. Initialise un projet Laravel avec Livewire, Tailwind CSS et Alpine.js.
2. Installe et configure stancl/tenancy pour le multi-tenant (un Tenant = un
   propriétaire de réseau de points).
3. Crée les migrations pour : Tenant, Point, Agent (avec rôles agent/propriétaire
   via un champ role), Operateur, Operation, Alimentation, Cloture — selon le
   modèle de données de référence ci-dessus.
4. Seed de données de démo réalistes en MRU : 1 tenant, 2 points, 3 agents,
   les 3 opérateurs (Bankily, Masrivi, Sedad) avec un barème de commission
   simple (1% du montant, arrondi à l'unité) pour chacun.
5. Authentification simple par numéro de téléphone + mot de passe (pas
   d'email — mes utilisateurs cibles n'en ont pas forcément un qu'ils
   consultent).
6. Mets en place la structure PWA de base (manifest.json, service worker
   minimal, icônes) — le contenu du service worker sera complété au Sprint 4.

Critère de fin de sprint : je peux me connecter avec un compte agent de
démo et voir une page d'accueil vide mais fonctionnelle, sans erreur.
```

---

## Sprint 2 — Écran de saisie agent (le cœur de l'app)

```
Sprint 2 : Écran de saisie agent

Construis l'écran principal de l'agent, celui qu'il utilise des dizaines de
fois par jour. Respecte scrupuleusement les exigences de simplicité du
contexte global.

1. Un seul écran (composant Livewire), pas de navigation à onglets multiples.
2. En haut : le solde de caisse actuel du point, mis à jour en temps réel.
3. Un sélecteur à deux gros boutons : "J'ai reçu de l'argent" (dépôt) /
   "J'ai donné de l'argent" (retrait) — jamais le mot "dépôt/retrait" affiché
   à l'utilisateur, seulement dans le code et les données.

Une maquette HTML de référence pour cet écran (structure, palette de
couleurs, typographie, comportement du clavier numérique, bascule FR/AR) est
fournie en Annexe A à la fin de ce document. Utilise-la comme référence
visuelle fidèle — reprends la palette blanc nacré et dégradés de bleu
(var(--ink) bleu profond pour le chrome/en-têtes, var(--sand)/var(--paper)
blanc nacré avec un léger dégradé pour les surfaces, var(--gold) bleu moyen
pour les accents neutres) avec le vert (var(--green)) réservé aux entrées
et le rouge (var(--rust)) réservé aux sorties — ce code couleur reste
fonctionnel et ne doit pas être remplacé par du bleu, même si le reste de
la palette passe au blanc nacré/bleu. Reprends aussi les polices (Space
Grotesk pour les titres/UI, Space Mono pour les chiffres, Cairo pour
l'arabe), et le comportement du clavier numérique custom. Adapte la
structure HTML/CSS statique en composant Livewire dynamique connecté à la
vraie base de données, sans perdre l'esprit du design.
4. Sélection de l'opérateur (Bankily/Masrivi/Sedad) par pastilles.
5. Champ numéro de téléphone du client (clavier téléphone, obligatoire).
6. Un lien discret "+ Ajouter nom / NNI (optionnel)" qui déplie deux champs
   optionnels sans alourdir le flux principal.
7. Saisie du montant au clavier numérique custom (pas le clavier natif du
   téléphone) avec calcul et affichage en direct de la commission calculée
   selon le barème de l'opérateur choisi.
8. Bouton de confirmation qui enregistre l'opération, génère le numéro de
   pièce automatiquement, met à jour le solde affiché, et vide le formulaire.
9. Sous le formulaire : liste des opérations du jour avec le code couleur
   vert (entrée) / rouge (sortie), heure, opérateur, numéro client.
10. L'interface doit fonctionner aussi bien en mobile qu'en desktop
    (formulaire en colonne de gauche, historique du jour en colonne de
    droite au-delà de 860px de large).

Critère de fin de sprint : un agent peut enregistrer un dépôt et un retrait
de bout en bout, voir son solde se mettre à jour, et voir les deux
opérations apparaître dans la liste du jour avec la bonne couleur.
```

---

## Sprint 3 — Bilingue Français / Arabe

```
Sprint 3 : Bilingue FR/AR

1. Mets en place un système de traduction Laravel classique (fichiers lang/fr
   et lang/ar) pour tous les textes de l'écran de saisie du Sprint 2.
2. Ajoute un sélecteur de langue (FR / عربي) accessible depuis l'écran
   principal, avec bascule instantanée sans rechargement de page complet.
3. Quand la langue arabe est active, l'intégralité de la mise en page doit
   passer en RTL (direction du texte, alignement, ordre des éléments dans les
   listes, position du sélecteur de langue lui-même) — pas seulement le texte
   traduit dans une mise en page qui reste LTR.
4. Utilise une police adaptée à l'arabe pour l'affichage arabe (ex: Cairo ou
   équivalent disponible), différente de la police latine utilisée en
   français.
5. Les chiffres restent en numérotation occidentale (1,2,3) même en mode
   arabe — c'est l'usage courant en Mauritanie.
6. Traduis les textes en respectant le ton "vocabulaire du quotidien" du
   contexte global, pas une traduction littérale et technique.
7. Le nom de l'app s'affiche "Le Comptoir" en français et "القنطوار" en
   arabe — jamais l'inverse, jamais un troisième nom ou une translittération.

Critère de fin de sprint : je peux basculer l'écran de saisie entre français
et arabe sans recharger la page, et tout — texte ET mise en page — s'inverse
correctement.
```

---

## Sprint 4 — Mode hors-ligne et synchronisation

```
Sprint 4 : Mode hors-ligne

1. Complète le service worker pour que l'écran de saisie (Sprint 2) reste
   pleinement utilisable sans connexion réseau : chargement de l'app,
   saisie d'opérations, calcul du solde local.
2. Les opérations enregistrées hors-ligne sont stockées localement
   (IndexedDB) avec un statut "en attente de synchronisation" et un
   identifiant temporaire.
3. Dès que la connexion revient, synchronisation automatique en arrière-plan
   vers le serveur, sans bloquer l'utilisateur et sans qu'il ait besoin de
   déclencher quoi que ce soit manuellement.
4. Gestion des conflits : si deux appareils du même point synchronisent des
   opérations créées hors-ligne en même temps, elles doivent toutes être
   conservées (jamais d'écrasement silencieux) — le numéro de pièce
   définitif est attribué au moment de la synchronisation, pas à la création
   locale.
5. Petit indicateur discret de statut de synchronisation dans l'interface
   (ex: point vert "à jour" / point orange "en attente"), sans être
   anxiogène pour l'utilisateur.

Critère de fin de sprint : je peux couper le réseau, enregistrer 3
opérations, remettre le réseau, et les voir apparaître correctement sur le
serveur sans perte ni doublon.
```

---

## Sprint 5 — Clôture de caisse et historique complet

```
Sprint 5 : Clôture de caisse et journal

1. Écran de clôture de caisse en fin de journée : l'agent saisit le montant
   qu'il compte physiquement, l'app le compare au solde théorique calculé,
   et affiche clairement l'écart s'il y en a un.
2. Une fois la clôture validée, toutes les opérations de la journée
   deviennent non modifiables (piste d'audit).
3. Écran Historique complet : liste filtrable par opérateur, par type,
   recherche par numéro de téléphone client.
4. Export du journal en Excel et PDF, avec la structure suivante en
   colonnes séparées : Date/Heure, N° de Pièce, Type, Client (nom + tel +
   NNI si renseignés), Entrées, Sorties, Solde, Commission, Observation —
   plus une ligne de total avec le solde net (Total Entrées − Total
   Sorties) en pied de tableau.

Critère de fin de sprint : je peux clôturer une journée de démo, voir
l'écart s'afficher si le compte ne tombe pas juste, et exporter le journal
du jour en Excel avec la structure attendue.
```

---

## Sprint 6 — Supervision propriétaire multi-points

```
Sprint 6 : Supervision propriétaire

1. Tableau de bord propriétaire : vue consolidée de tous les points du
   tenant — solde de caisse, nombre d'opérations du jour, bénéfices
   cumulés par point et au total.
2. Écran "Alimentation de caisse" : le propriétaire choisit un point,
   enregistre un montant injecté avec une note optionnelle. Ce mouvement
   est bien distinct des opérations clients dans le modèle de données et
   dans l'affichage.
3. Alerte visible sur le tableau de bord si un écart de caisse a été
   détecté à la clôture d'un point, ou si un agent n'a pas encore fait sa
   clôture du jour alors que l'heure habituelle est dépassée.
4. Rapport de rentabilité par point : capital injecté (alimentations) vs
   commissions générées sur la période choisie.
5. Cet écran doit lui aussi être bilingue et responsive, en réutilisant les
   composants et le système de traduction des sprints précédents.

Critère de fin de sprint : je peux me connecter en tant que propriétaire
avec 2 points de démo, voir leurs soldes consolidés, enregistrer une
alimentation sur l'un des deux, et voir le solde de caisse de ce point se
mettre à jour en conséquence.
```

---

## Sprint 7 — Rapports et plans tarifaires

```
Sprint 7 : Rapports et gestion des plans

1. Rapports journalier / hebdomadaire / mensuel des commissions générées,
   par point et consolidé, avec export Excel/PDF.
2. Mets en place les 3 plans tarifaires dans le modèle Tenant :
   - Solo (1 point) : saisie, historique, clôture, export
   - Réseau (2 à 5 points) : + supervision multi-points, alimentation,
     alertes
   - Entreprise (6+ points) : + statistiques avancées, rôles multiples
3. Verrouille l'accès aux fonctionnalités de supervision (Sprint 6) selon
   le plan du tenant — un tenant Solo ne doit pas voir les écrans
   multi-points.
4. Ajoute un essai gratuit de 14 jours activable automatiquement à la
   création d'un tenant sur le plan Solo, avec passage en lecture seule
   (pas de suppression de données) à l'expiration si aucun paiement n'a été
   enregistré.

Critère de fin de sprint : un tenant créé sur le plan Solo ne voit pas les
écrans de supervision multi-points ; passer son plan à Réseau en base les
débloque immédiatement sans redéploiement.
```

---

## Notes pour la suite (hors sprints MVP, à ne traiter qu'une fois le pilote validé)

- Back-office Administration Syslog (gestion des tenants, facturation, support)
- Intégration du paiement d'abonnement via le circuit marchand Bankily
- Ces deux chantiers sont volontairement hors du prompt ci-dessus : ils ne
  doivent démarrer qu'une fois plusieurs clients payants acquis via le
  pilote terrain, pas avant.

---

## Annexe A — Maquette HTML de référence (écran de saisie agent)

Ce fichier est une maquette statique autonome (HTML/CSS/JS vanilla, sans
dépendance backend), à ouvrir dans un navigateur pour voir le rendu exact.
Elle sert de référence visuelle et comportementale pour le Sprint 2 —
palette de couleurs, typographie, disposition responsive mobile/desktop,
bascule FR/AR avec RTL, clavier numérique custom, code couleur vert/rouge
des entrées/sorties.

```html
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Le Comptoir / القنطوار — Maquette</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&family=Inter:wght@400;500;600;700&family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#123A66;
    --ink-soft:#5C7CA3;
    --sand:#FBFCFF;
    --sand-deep:#E4EDF8;
    --gold:#2F6FB0;
    --gold-deep:#1F4D80;
    --rust:#A8452C;
    --rust-deep:#8A3620;
    --green:#2E6B4E;
    --green-deep:#1F5138;
    --paper:#FDFEFF;
    --line:#D7E3F0;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  body{
    background:linear-gradient(180deg,#0A2242,#123A66 40%);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:32px 16px;
    font-family:'Inter',sans-serif;
  }
  .stage{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:14px;
  }
  .stage-label{
    color:#B9C2D6;
    font-family:'Space Grotesk',sans-serif;
    font-size:12px;
    letter-spacing:.16em;
    text-transform:uppercase;
    text-align:center;
  }
  .phone{
    width:375px;
    max-width:92vw;
    height:800px;
    max-height:88vh;
    background:linear-gradient(135deg, #FDFEFF 0%, #F1F6FC 45%, #FBFDFF 70%, #EEF4FB 100%);
    border-radius:38px;
    border:8px solid var(--ink);
    box-shadow:0 30px 60px rgba(0,0,0,.45), inset 0 0 0 1px rgba(255,255,255,.04);
    overflow:hidden;
    position:relative;
    display:flex;
    flex-direction:column;
    transition:font-family .1s;
  }
  .phone.ar{ font-family:'Cairo',sans-serif; }
  .notch{
    position:absolute;
    top:0; left:50%;
    transform:translateX(-50%);
    width:120px; height:22px;
    background:var(--ink);
    border-radius:0 0 16px 16px;
    z-index:20;
  }

  /* Lang switcher */
  .lang-switch{
    position:absolute;
    top:34px; z-index:25;
    display:flex;
    background:rgba(255,255,255,.1);
    border-radius:20px;
    padding:3px;
  }
  .phone:not(.ar) .lang-switch{ left:18px; }
  .phone.ar .lang-switch{ right:18px; }
  .lang-btn{
    border:none;
    background:transparent;
    color:#9AA6C0;
    font-family:'Space Grotesk',sans-serif;
    font-size:11px;
    font-weight:700;
    padding:6px 11px;
    border-radius:16px;
    cursor:pointer;
  }
  .lang-btn.active{
    background:var(--sand);
    color:var(--ink);
  }

  /* Header / ledger tape */
  .tape{
    background:var(--ink);
    color:var(--sand);
    padding:60px 20px 22px;
    position:relative;
    z-index:5;
  }
  .tape-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    font-family:'Space Grotesk',sans-serif;
  }
  .phone.ar .tape-top{ font-family:'Cairo',sans-serif; }
  .tape-point{
    font-size:13px;
    color:#9AA6C0;
    letter-spacing:.02em;
  }
  .app-name{
    display:block;
    font-family:'Space Grotesk',sans-serif;
    font-weight:700;
    font-size:16px;
    color:var(--sand);
    letter-spacing:.01em;
    margin-bottom:8px;
  }
  .phone.ar .app-name{ font-family:'Cairo',sans-serif; }
  .tape-point b{ color:var(--sand); font-weight:600; display:block; margin-top:2px; font-size:14px;}
  .solde-label{
    font-size:12px;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:#8C97B4;
    margin-top:20px;
    font-weight:600;
  }
  .solde-value{
    font-family:'Space Mono',monospace;
    font-weight:700;
    font-size:42px;
    letter-spacing:-.01em;
    margin-top:6px;
    font-variant-numeric:tabular-nums;
  }
  .solde-value span{ font-size:17px; color:#8C97B4; font-family:'Space Grotesk',sans-serif; font-weight:500; margin-left:4px;}
  .phone.ar .solde-value span{ font-family:'Cairo',sans-serif; margin-left:0; margin-right:4px; }
  .tape-perf{
    height:14px;
    margin:0 -20px -14px;
    background-image:radial-gradient(circle, var(--paper) 5px, transparent 5.5px);
    background-size:22px 14px;
    background-position:11px 0;
    position:relative;
    top:12px;
  }

  /* Body scroll */
  .body{
    flex:1;
    overflow-y:auto;
    padding:22px 20px 0;
  }

  .toggle-row{
    display:flex;
    background:var(--sand-deep);
    border-radius:16px;
    padding:5px;
    gap:5px;
  }
  .toggle-btn{
    flex:1;
    border:none;
    background:transparent;
    padding:16px 0;
    border-radius:12px;
    font-family:'Space Grotesk',sans-serif;
    font-weight:700;
    font-size:16px;
    color:var(--ink-soft);
    cursor:pointer;
    transition:background .2s, color .2s, box-shadow .2s;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
  }
  .phone.ar .toggle-btn{ font-family:'Cairo',sans-serif; font-size:15px; }
  .toggle-btn .ic{ font-size:18px; }
  .toggle-btn.active-depot{
    background:var(--green);
    color:#fff;
    box-shadow:0 4px 10px rgba(46,107,78,.35);
  }
  .toggle-btn.active-retrait{
    background:var(--rust);
    color:#fff;
    box-shadow:0 4px 10px rgba(168,69,44,.35);
  }

  .section-label{
    font-family:'Space Grotesk',sans-serif;
    font-size:12px;
    letter-spacing:.06em;
    font-weight:600;
    color:var(--ink-soft);
    margin:22px 0 10px;
  }
  .phone.ar .section-label{ font-family:'Cairo',sans-serif; }

  .chips{
    display:flex;
    gap:8px;
  }
  .chip{
    flex:1;
    text-align:center;
    padding:14px 6px;
    border-radius:12px;
    border:1.5px solid var(--line);
    background:var(--paper);
    font-family:'Space Grotesk',sans-serif;
    font-size:13.5px;
    font-weight:700;
    color:var(--ink-soft);
    cursor:pointer;
    transition:all .15s;
  }
  .phone.ar .chip{ font-family:'Cairo',sans-serif; }
  .chip.active{
    border-color:var(--ink);
    background:var(--ink);
    color:var(--sand);
  }

  .phone-input-wrap{
    display:flex;
    align-items:center;
    gap:10px;
    background:var(--paper);
    border:1.5px solid var(--line);
    border-radius:14px;
    padding:14px 16px;
  }
  .phone-ic{
    font-size:18px;
    color:var(--ink-soft);
    flex-shrink:0;
  }
  .phone-input{
    flex:1;
    border:none;
    background:transparent;
    outline:none;
    font-family:'Space Mono',monospace;
    font-size:19px;
    font-weight:700;
    color:var(--ink);
    letter-spacing:.04em;
  }
  .phone-input::placeholder{
    color:#B7AE97;
    font-weight:700;
  }

  .optional-toggle{
    margin-top:10px;
    text-align:center;
    font-family:'Space Grotesk',sans-serif;
    font-size:12px;
    font-weight:600;
    color:var(--ink-soft);
    cursor:pointer;
    padding:6px 0;
  }
  .phone.ar .optional-toggle{ font-family:'Cairo',sans-serif; }
  .optional-fields{
    max-height:0;
    overflow:hidden;
    transition:max-height .25s ease;
    display:flex;
    flex-direction:column;
    gap:8px;
  }
  .optional-fields.open{ max-height:160px; margin-top:6px; }
  .mini-input-wrap{
    display:flex;
    align-items:center;
    gap:10px;
    background:var(--paper);
    border:1.5px dashed var(--line);
    border-radius:12px;
    padding:11px 14px;
  }
  .mini-input-wrap .phone-input{ font-size:15px; font-family:'Inter',sans-serif; font-weight:600; }

  .amount-display{
    margin-top:22px;
    text-align:center;
    padding:0 4px;
  }
  .amount-display .cur{ font-family:'Space Grotesk',sans-serif; font-size:13px; color:var(--ink-soft); letter-spacing:.04em; font-weight:600;}
  .phone.ar .amount-display .cur{ font-family:'Cairo',sans-serif; }
  .amount-num{
    font-family:'Space Mono',monospace;
    font-weight:700;
    font-size:48px;
    color:var(--ink);
    font-variant-numeric:tabular-nums;
    line-height:1;
    margin-top:6px;
  }
  .amount-num .cursor-blink{
    display:inline-block;
    width:3px;
    height:36px;
    background:var(--gold-deep);
    margin-left:4px;
    vertical-align:-6px;
    animation:blink 1s step-end infinite;
  }
  @keyframes blink{ 50%{opacity:0;} }
  .commission-note{
    margin-top:8px;
    font-family:'Space Grotesk',sans-serif;
    font-size:12.5px;
    font-weight:600;
    color:var(--green-deep);
    min-height:16px;
  }
  .phone.ar .commission-note{ font-family:'Cairo',sans-serif; }

  .keypad{
    margin-top:18px;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
  }
  .key{
    background:var(--paper);
    border:1.5px solid var(--line);
    border-radius:14px;
    padding:18px 0;
    text-align:center;
    font-family:'Space Mono',monospace;
    font-size:20px;
    font-weight:700;
    color:var(--ink);
    cursor:pointer;
    user-select:none;
    transition:background .12s, transform .06s;
  }
  .key:active{ transform:scale(.94); background:var(--sand-deep); }
  .key.func{ font-family:'Space Grotesk',sans-serif; font-size:13px; color:var(--ink-soft); font-weight:700; }
  .phone.ar .key.func{ font-family:'Cairo',sans-serif; font-size:13px; }

  .confirm-btn{
    margin-top:16px;
    width:100%;
    border:none;
    padding:19px 0;
    border-radius:16px;
    font-family:'Space Grotesk',sans-serif;
    font-weight:700;
    font-size:16px;
    color:#fff;
    cursor:pointer;
    transition:background .2s, box-shadow .2s;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
  }
  .phone.ar .confirm-btn{ font-family:'Cairo',sans-serif; }
  .confirm-btn.depot{ background:var(--green-deep); box-shadow:0 8px 18px rgba(31,81,56,.35); }
  .confirm-btn.retrait{ background:var(--rust-deep); box-shadow:0 8px 18px rgba(138,54,32,.35); }

  .history{
    margin-top:22px;
    padding-bottom:120px;
  }
  .ledger-legend{
    display:flex;
    gap:16px;
    margin-top:-2px;
    margin-bottom:6px;
  }
  .legend-item{
    display:flex;
    align-items:center;
    gap:6px;
    font-family:'Space Grotesk',sans-serif;
    font-size:11px;
    font-weight:600;
    color:var(--ink-soft);
  }
  .phone.ar .legend-item{ font-family:'Cairo',sans-serif; }
  .legend-dot{ width:8px; height:8px; border-radius:50%; }
  .legend-dot.d{ background:var(--green); }
  .legend-dot.r{ background:var(--rust); }
  .ledger-line{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 0;
    border-bottom:1px dashed var(--line);
  }
  .ledger-dot{
    width:9px; height:9px; border-radius:50%;
    flex-shrink:0;
  }
  .ledger-dot.d{ background:var(--green); }
  .ledger-dot.r{ background:var(--rust); }
  .ledger-mid{ flex:1; }
  .ledger-op{ font-family:'Space Grotesk',sans-serif; font-size:14px; font-weight:700; color:var(--ink); }
  .phone.ar .ledger-op{ font-family:'Cairo',sans-serif; }
  .ledger-time{ font-family:'Inter',sans-serif; font-size:11px; color:var(--ink-soft); margin-top:2px;}
  .ledger-amt{ font-family:'Space Mono',monospace; font-weight:700; font-size:15px; }
  .ledger-amt.d{ color:var(--green-deep); }
  .ledger-amt.r{ color:var(--rust-deep); }

  /* Bottom summary bar */
  .summary-bar{
    position:absolute;
    left:14px; right:14px; bottom:16px;
    background:var(--ink);
    border-radius:18px;
    padding:16px 18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 12px 28px rgba(0,0,0,.35);
    z-index:15;
  }
  .summary-item{ text-align:left; }
  .phone.ar .summary-item{ text-align:right; }
  .summary-label{ font-family:'Space Grotesk',sans-serif; font-size:10px; letter-spacing:.06em; font-weight:600; color:#8C97B4;}
  .phone.ar .summary-label{ font-family:'Cairo',sans-serif; }
  .summary-value{ font-family:'Space Mono',monospace; font-weight:700; font-size:17px; color:var(--sand); margin-top:3px;}
  .summary-value.green{ color:#6FCB9F; }

  ::-webkit-scrollbar{ width:0; }

  /* ---------- Responsive: layout containers (no visual change on mobile) ---------- */
  .layout, .col-form, .col-history{ display:contents; }

  /* ---------- Desktop / tablet layout ---------- */
  @media (min-width:860px){
    body{ padding:48px 24px; align-items:flex-start; justify-content:center; }
    .stage{ width:100%; max-width:980px; }
    .stage-label{ text-align:left; font-size:13px; }

    .phone{
      width:100%;
      max-width:980px;
      height:auto;
      max-height:none;
      border:1px solid var(--line);
      border-radius:22px;
      box-shadow:0 24px 60px rgba(10,15,30,.28);
    }
    .notch{ display:none; }

    .tape{ padding:24px 36px 26px; }
    .tape-top{ align-items:center; }
    .lang-switch{ top:24px; }
    .phone:not(.ar) .lang-switch{ left:auto; right:36px; }
    .phone.ar .lang-switch{ right:auto; left:36px; }
    .solde-value{ font-size:34px; }
    .tape-perf{ display:none; }

    .body{ padding:32px 36px 36px; overflow:visible; }
    .layout{
      display:grid;
      grid-template-columns:1.05fr 0.95fr;
      gap:40px;
      align-items:start;
    }
    .col-form, .col-history{ display:block; }

    .keypad{ max-width:340px; }
    .confirm-btn{ max-width:340px; }
    .history{ padding-bottom:8px; max-height:520px; overflow-y:auto; padding-right:6px; }
    .col-history{ border-left:1px solid var(--line); padding-left:36px; }
    .phone.ar .col-history{ border-left:none; border-right:1px solid var(--line); padding-left:0; padding-right:36px; }

    .summary-bar{
      position:static;
      margin:0 36px 32px;
      border-radius:16px;
    }
  }

  @media (min-width:860px) and (max-width:1099px){
    .layout{ grid-template-columns:1fr 1fr; gap:28px; }
  }
</style>
</head>
<body>

<div class="stage">
  <div class="stage-label" id="stageLabel">Le Comptoir — Écran de saisie, bilingue FR / AR, responsive</div>
  <div class="phone" id="phone" dir="ltr">
    <div class="notch"></div>
    <div class="lang-switch">
      <button class="lang-btn active" id="btnFr" onclick="setLang('fr')">FR</button>
      <button class="lang-btn" id="btnAr" onclick="setLang('ar')">عربي</button>
    </div>

    <div class="tape">
      <div class="tape-top">
        <div class="tape-point">
          <span class="app-name" id="appName">Le Comptoir</span>
          <span id="lblPoint">Point</span>
          <b id="pointName">Marché Capitale — Kiosque 3</b>
        </div>
      </div>
      <div class="solde-label" id="lblSolde">Ce que vous avez en caisse</div>
      <div class="solde-value" id="soldeValue">184 500<span id="curTag">MRU</span></div>
      <div class="tape-perf"></div>
    </div>

    <div class="body">
     <div class="layout">
     <div class="col-form">
      <div class="toggle-row">
        <button class="toggle-btn active-depot" id="btnDepot" onclick="setMode('depot')">
          <span class="ic">↓</span><span id="lblDepot">J'ai reçu de l'argent</span>
        </button>
        <button class="toggle-btn" id="btnRetrait" onclick="setMode('retrait')">
          <span class="ic">↑</span><span id="lblRetrait">J'ai donné de l'argent</span>
        </button>
      </div>

      <div class="section-label" id="lblOperateur">Quel opérateur ?</div>
      <div class="chips">
        <div class="chip active" onclick="setChip(this)">Bankily</div>
        <div class="chip" onclick="setChip(this)">Masrivi</div>
        <div class="chip" onclick="setChip(this)">Sedad</div>
      </div>

      <div class="section-label" id="lblTel">Numéro du client</div>
      <div class="phone-input-wrap">
        <span class="phone-ic">☎</span>
        <input type="tel" id="phoneInput" class="phone-input" inputmode="numeric" placeholder="XX XX XX XX" maxlength="11">
      </div>

      <div class="optional-toggle" id="optToggle" onclick="toggleOptional()">
        <span id="lblOptToggle">+ Ajouter nom / NNI (optionnel)</span>
      </div>
      <div class="optional-fields" id="optionalFields">
        <div class="mini-input-wrap">
          <span class="phone-ic">👤</span>
          <input type="text" id="nameInput" class="phone-input" placeholder="Nom du client">
        </div>
        <div class="mini-input-wrap">
          <span class="phone-ic">🪪</span>
          <input type="text" id="nniInput" class="phone-input" inputmode="numeric" placeholder="NNI">
        </div>
      </div>

      <div class="amount-display">
        <div class="cur" id="lblMontant">Combien (MRU) ?</div>
        <div class="amount-num" id="amountNum">15 000<span class="cursor-blink"></span></div>
        <div class="commission-note" id="commissionNote"></div>
      </div>

      <div class="keypad">
        <div class="key" onclick="tap('1')">1</div>
        <div class="key" onclick="tap('2')">2</div>
        <div class="key" onclick="tap('3')">3</div>
        <div class="key" onclick="tap('4')">4</div>
        <div class="key" onclick="tap('5')">5</div>
        <div class="key" onclick="tap('6')">6</div>
        <div class="key" onclick="tap('7')">7</div>
        <div class="key" onclick="tap('8')">8</div>
        <div class="key" onclick="tap('9')">9</div>
        <div class="key func" onclick="clearAmt()" id="lblEffacer">Effacer</div>
        <div class="key" onclick="tap('0')">0</div>
        <div class="key func" onclick="tap('000')">000</div>
      </div>

      <button class="confirm-btn depot" id="confirmBtn" onclick="confirmOp()">
        <span id="lblConfirm">✓ C'est fait</span>
      </button>

     </div>
     <div class="col-history">
      <div class="section-label" id="lblToday">Aujourd'hui</div>
      <div class="ledger-legend">
        <span class="legend-item"><span class="legend-dot d"></span><span id="lblEntree">Entrée</span></span>
        <span class="legend-item"><span class="legend-dot r"></span><span id="lblSortie">Sortie</span></span>
      </div>
      <div class="history" id="historyList"></div>
     </div>
     </div>
    </div>

    <div class="summary-bar">
      <div class="summary-item">
        <div class="summary-label" id="lblBenefice">Ce que vous avez gagné</div>
        <div class="summary-value green">+ 4 250 MRU</div>
      </div>
      <div class="summary-item">
        <div class="summary-label" id="lblOps">Opérations</div>
        <div class="summary-value">12</div>
      </div>
    </div>
  </div>
</div>

<script>
  const T = {
    fr: {
      stage:"Le Comptoir — Écran de saisie, bilingue FR / AR, responsive",
      appName:"Le Comptoir",
      point:"Point",
      pointName:"Marché Capitale — Kiosque 3",
      solde:"Ce que vous avez en caisse",
      cur:"MRU",
      depot:"J'ai reçu de l'argent",
      retrait:"J'ai donné de l'argent",
      operateur:"Quel opérateur ?",
      tel:"Numéro du client",
      optToggle:"+ Ajouter nom / NNI (optionnel)",
      montant:"Combien (MRU) ?",
      commission:"Commission : ",
      effacer:"Effacer",
      confirmDepot:"✓ C'est fait",
      confirmRetrait:"✓ C'est fait",
      today:"Aujourd'hui",
      entree:"Entrée",
      sortie:"Sortie",
      benefice:"Ce que vous avez gagné",
      ops:"Opérations",
      opDepot:"Reçu",
      opRetrait:"Donné"
    },
    ar: {
      stage:"القنطوار — شاشة التسجيل، بالفرنسية والعربية",
      appName:"القنطوار",
      point:"النقطة",
      pointName:"لكصر — كشك 3",
      solde:"المبلغ الموجود في الصندوق",
      cur:"أوقية",
      depot:"استلمت مالاً",
      retrait:"دفعت مالاً",
      operateur:"أي مشغل؟",
      tel:"رقم الزبون",
      optToggle:"+ إضافة الاسم / البطاقة الوطنية (اختياري)",
      montant:"كم المبلغ؟",
      commission:"العمولة: ",
      effacer:"مسح",
      confirmDepot:"✓ تم",
      confirmRetrait:"✓ تم",
      today:"اليوم",
      entree:"دخول",
      sortie:"خروج",
      benefice:"ما ربحته",
      ops:"العمليات",
      opDepot:"استلام",
      opRetrait:"دفع"
    }
  };

  let lang = 'fr';
  let mode = 'depot';
  let amount = '15000';
  let solde = 184500;
  let history = [
    {type:'depot', op:'Bankily', time:'11:42', amt:20000, tel:'22 45 67 89'},
    {type:'retrait', op:'Masrivi', time:'10:58', amt:8500, tel:'36 12 04 55'},
    {type:'depot', op:'Bankily', time:'09:20', amt:35000, tel:'41 78 90 21'}
  ];

  function fmt(n){ return n.toLocaleString('fr-FR').replace(/,/g,' '); }

  function setLang(l){
    lang = l;
    const t = T[l];
    document.getElementById('phone').className = 'phone' + (l==='ar' ? ' ar' : '');
    document.getElementById('phone').setAttribute('dir', l==='ar' ? 'rtl' : 'ltr');
    document.getElementById('btnFr').classList.toggle('active', l==='fr');
    document.getElementById('btnAr').classList.toggle('active', l==='ar');
    document.getElementById('stageLabel').textContent = t.stage;
    document.getElementById('appName').textContent = t.appName;
    document.getElementById('lblPoint').textContent = t.point;
    document.getElementById('pointName').textContent = t.pointName;
    document.getElementById('lblSolde').textContent = t.solde;
    document.getElementById('curTag').textContent = t.cur;
    document.getElementById('lblDepot').textContent = t.depot;
    document.getElementById('lblRetrait').textContent = t.retrait;
    document.getElementById('lblOperateur').textContent = t.operateur;
    document.getElementById('lblTel').textContent = t.tel;
    document.getElementById('lblOptToggle').textContent = t.optToggle;
    document.getElementById('lblMontant').textContent = t.montant;
    document.getElementById('lblEffacer').textContent = t.effacer;
    document.getElementById('lblConfirm').textContent = mode==='depot' ? t.confirmDepot : t.confirmRetrait;
    document.getElementById('lblToday').textContent = t.today;
    document.getElementById('lblEntree').textContent = t.entree;
    document.getElementById('lblSortie').textContent = t.sortie;
    document.getElementById('lblBenefice').textContent = t.benefice;
    document.getElementById('lblOps').textContent = t.ops;
    renderHistory();
  }

  function setMode(m){
    mode = m;
    const t = T[lang];
    document.getElementById('btnDepot').className = 'toggle-btn' + (m==='depot' ? ' active-depot' : '');
    document.getElementById('btnRetrait').className = 'toggle-btn' + (m==='retrait' ? ' active-retrait' : '');
    const btn = document.getElementById('confirmBtn');
    btn.className = 'confirm-btn ' + m;
    document.getElementById('lblConfirm').textContent = m==='depot' ? t.confirmDepot : t.confirmRetrait;
  }

  function setChip(el){
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
  }

  function tap(d){
    if(amount === '0') amount = '';
    if(amount.replace(/\s/g,'').length >= 9) return;
    amount += d;
    document.getElementById('amountNum').innerHTML = fmt(Number(amount)) + '<span class="cursor-blink"></span>';
    updateCommission();
  }

  function updateCommission(){
    const val = Number(amount) || 0;
    const commission = Math.round(val * 0.01); // barème simplifié 1%, à affiner par opérateur
    document.getElementById('commissionNote').textContent = val > 0 ? T[lang].commission + fmt(commission) + ' MRU' : '';
  }

  function toggleOptional(){
    const el = document.getElementById('optionalFields');
    el.classList.toggle('open');
  }

  function clearAmt(){
    amount = '0';
    document.getElementById('amountNum').innerHTML = '0<span class="cursor-blink"></span>';
    document.getElementById('commissionNote').textContent = '';
  }

  function renderHistory(){
    const t = T[lang];
    const list = document.getElementById('historyList');
    list.innerHTML = '';
    history.forEach(h => {
      const line = document.createElement('div');
      line.className = 'ledger-line';
      const isDepot = h.type === 'depot';
      line.innerHTML = `
        <div class="ledger-dot ${isDepot?'d':'r'}"></div>
        <div class="ledger-mid">
          <div class="ledger-op">${isDepot?t.opDepot:t.opRetrait} — ${h.op}</div>
          <div class="ledger-time">${h.time} · ${h.tel || ''}</div>
        </div>
        <div class="ledger-amt ${isDepot?'d':'r'}">${isDepot?'+':'−'} ${fmt(h.amt)}</div>
      `;
      list.appendChild(line);
    });
  }

  function confirmOp(){
    const val = Number(amount) || 0;
    if(val === 0) return;
    solde = mode === 'depot' ? solde + val : solde - val;
    document.getElementById('soldeValue').innerHTML = fmt(solde) + '<span id="curTag">' + T[lang].cur + '</span>';

    const opName = document.querySelector('.chip.active').textContent;
    const now = new Date();
    const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
    const telVal = document.getElementById('phoneInput').value || '—';
    history.unshift({type:mode, op:opName, time:time, amt:val, tel:telVal});
    renderHistory();
    clearAmt();
    document.getElementById('phoneInput').value = '';
    document.getElementById('nameInput').value = '';
    document.getElementById('nniInput').value = '';
  }

  document.getElementById('phoneInput').addEventListener('input', function(e){
    let digits = e.target.value.replace(/\D/g,'').slice(0,8);
    e.target.value = digits.replace(/(\d{2})(?=\d)/g, '$1 ').trim();
  });

  renderHistory();
</script>

</body>
</html>

```

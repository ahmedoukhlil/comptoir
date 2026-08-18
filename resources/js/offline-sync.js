import { listerOperationsEnAttente, marquerSynchronisee, compterEnAttente } from './offline-db.js';

let synchronisationEnCours = false;

function jetonCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export async function synchroniser() {
    if (synchronisationEnCours || ! navigator.onLine) {
        return;
    }

    const enAttente = await listerOperationsEnAttente();

    if (enAttente.length === 0) {
        window.dispatchEvent(new CustomEvent('comptoir:sync-statut', { detail: { enAttente: 0 } }));

        return;
    }

    synchronisationEnCours = true;

    try {
        const reponse = await fetch('/api/operations/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': jetonCsrf(),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                operations: enAttente.map(({ statut, ...op }) => op),
            }),
        });

        if (! reponse.ok) {
            throw new Error('Échec de synchronisation');
        }

        const { resultats } = await reponse.json();

        for (const resultat of resultats) {
            await marquerSynchronisee(resultat.uuid_client, resultat.numero_piece);
        }

        window.dispatchEvent(new CustomEvent('comptoir:sync-terminee', { detail: { resultats } }));
    } catch (e) {
        // Silencieux : on retentera au prochain événement online ou au prochain intervalle.
    } finally {
        synchronisationEnCours = false;
        const restant = await compterEnAttente();
        window.dispatchEvent(new CustomEvent('comptoir:sync-statut', { detail: { enAttente: restant } }));
    }
}

export function demarrerSynchronisationAutomatique() {
    window.addEventListener('online', synchroniser);
    document.addEventListener('livewire:navigated', synchroniser);

    // Filet de sécurité : retente périodiquement au cas où l'événement
    // 'online' du navigateur ne se déclenche pas de façon fiable.
    setInterval(synchroniser, 15000);

    synchroniser();
}

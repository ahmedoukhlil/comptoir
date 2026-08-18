const DB_NAME = 'comptoir-offline';
const DB_VERSION = 1;
const STORE = 'operations_en_attente';

function ouvrirDb() {
    return new Promise((resolve, reject) => {
        const requete = indexedDB.open(DB_NAME, DB_VERSION);

        requete.onupgradeneeded = () => {
            const db = requete.result;
            if (! db.objectStoreNames.contains(STORE)) {
                const store = db.createObjectStore(STORE, { keyPath: 'uuid_client' });
                store.createIndex('statut', 'statut');
            }
        };

        requete.onsuccess = () => resolve(requete.result);
        requete.onerror = () => reject(requete.error);
    });
}

export async function ajouterOperationLocale(operation) {
    const db = await ouvrirDb();

    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        tx.objectStore(STORE).put({ ...operation, statut: 'en_attente' });
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

export async function listerOperationsEnAttente() {
    const db = await ouvrirDb();

    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readonly');
        const requete = tx.objectStore(STORE).getAll();
        requete.onsuccess = () => resolve(requete.result.filter(o => o.statut === 'en_attente'));
        requete.onerror = () => reject(requete.error);
    });
}

export async function listerToutesLesOperationsLocales() {
    const db = await ouvrirDb();

    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readonly');
        const requete = tx.objectStore(STORE).getAll();
        requete.onsuccess = () => resolve(requete.result);
        requete.onerror = () => reject(requete.error);
    });
}

export async function marquerSynchronisee(uuidClient, numeroPiece) {
    const db = await ouvrirDb();

    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        const store = tx.objectStore(STORE);
        const requete = store.get(uuidClient);

        requete.onsuccess = () => {
            const enregistrement = requete.result;
            if (enregistrement) {
                enregistrement.statut = 'synchronisee';
                enregistrement.numero_piece = numeroPiece;
                store.put(enregistrement);
            }
        };

        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

export async function compterEnAttente() {
    const operations = await listerOperationsEnAttente();

    return operations.length;
}

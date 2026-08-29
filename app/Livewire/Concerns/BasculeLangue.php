<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\App;

trait BasculeLangue
{
    public function changerLangue(string $locale): void
    {
        if (! in_array($locale, ['fr', 'ar'], true)) {
            return;
        }

        session(['locale' => $locale]);
        App::setLocale($locale);

        $this->dispatch('langue-changee', locale: $locale, traductions: [
            'erreurTelephoneDigits' => __('caisse.erreur_telephone_digits'),
            'erreurMontantVide' => __('caisse.erreur_montant_vide'),
            'erreurSoldeInsuffisant' => __('caisse.erreur_solde_insuffisant'),
            'erreurCashInsuffisant' => __('caisse.erreur_cash_insuffisant'),
            'erreurTypeVide' => __('caisse.erreur_type_vide'),
            'erreurOperateurVide' => __('caisse.erreur_operateur_vide'),
            'syncEnAttente' => __('caisse.sync_en_attente'),
            'syncBadgeAttente' => __('caisse.sync_badge_attente'),
            'clotureEcartAucun' => __('caisse.cloture_ecart_aucun'),
            'clotureEcartPositif' => __('caisse.cloture_ecart_positif'),
            'clotureEcartNegatif' => __('caisse.cloture_ecart_negatif'),
            'libelleDepot' => __('caisse.depot'),
            'libelleRetrait' => __('caisse.retrait'),
            'libelleRetraitBeneficiaire' => __('caisse.retrait_beneficiaire'),
            'cashRecuClient' => __('caisse.cash_recu_client'),
            'cashRemisClient' => __('caisse.cash_remis_client'),
        ]);
    }
}

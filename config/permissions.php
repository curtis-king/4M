<?php

return [

    /*
    | Catalogue des permissions applicatives, groupées par rubrique métier.
    | Utilisé pour afficher les "vraies appellations" dans l'écran
    | Rôles & permissions et dans la matrice de gestion.
    */

    'view dashboard' => ['label' => 'Voir le tableau de bord', 'group' => 'Pilotage'],

    'view clients' => ['label' => 'Voir les clients', 'group' => 'Clients'],
    'create client' => ['label' => 'Créer un client', 'group' => 'Clients'],
    'edit client' => ['label' => 'Modifier un client', 'group' => 'Clients'],
    'delete client' => ['label' => 'Supprimer un client', 'group' => 'Clients'],

    'view devis' => ['label' => 'Voir les devis', 'group' => 'Devis'],
    'create devis' => ['label' => 'Créer un devis', 'group' => 'Devis'],
    'edit devis' => ['label' => 'Modifier un devis', 'group' => 'Devis'],
    'delete devis' => ['label' => 'Supprimer un devis', 'group' => 'Devis'],
    'print devis' => ['label' => 'Imprimer un devis', 'group' => 'Devis'],
    'convert devis' => ['label' => 'Convertir un devis en facture', 'group' => 'Devis'],

    'view invoices' => ['label' => 'Voir les factures', 'group' => 'Facturation'],
    'create invoice' => ['label' => 'Créer une facture', 'group' => 'Facturation'],
    'edit invoice' => ['label' => 'Modifier une facture', 'group' => 'Facturation'],
    'delete invoice' => ['label' => 'Supprimer une facture', 'group' => 'Facturation'],
    'print invoice' => ['label' => 'Imprimer une facture', 'group' => 'Facturation'],
    'view financial data' => ['label' => 'Voir le chiffre d\'affaires et les montants', 'group' => 'Facturation'],

    'export financial data' => ['label' => 'Exporter les données comptables (Sage/CSV)', 'group' => 'Comptabilité'],
    'import financial data' => ['label' => 'Importer des données depuis un fichier Excel', 'group' => 'Comptabilité'],

    'manage payments' => ['label' => 'Gérer les paiements', 'group' => 'Paiements'],

    'view visits' => ['label' => 'Voir les visites', 'group' => 'Visites'],
    'create visit' => ['label' => 'Créer une visite', 'group' => 'Visites'],
    'edit visit' => ['label' => 'Modifier une visite', 'group' => 'Visites'],
    'delete visit' => ['label' => 'Supprimer une visite', 'group' => 'Visites'],

    'manage insurers' => ['label' => 'Gérer les compagnies d\'assurance', 'group' => 'Assurance'],

    'manage services' => ['label' => 'Gérer la grille des services', 'group' => 'Laboratoire'],
    'manage reagents' => ['label' => 'Gérer le stock de réactifs', 'group' => 'Laboratoire'],

    'view users' => ['label' => 'Voir les utilisateurs', 'group' => 'Utilisateurs & rôles'],
    'create user' => ['label' => 'Créer un utilisateur', 'group' => 'Utilisateurs & rôles'],
    'edit user' => ['label' => 'Modifier un utilisateur', 'group' => 'Utilisateurs & rôles'],
    'delete user' => ['label' => 'Supprimer un utilisateur', 'group' => 'Utilisateurs & rôles'],
    'manage users' => ['label' => 'Gérer les utilisateurs', 'group' => 'Utilisateurs & rôles'],
    'view roles' => ['label' => 'Voir les rôles', 'group' => 'Utilisateurs & rôles'],
    'manage roles' => ['label' => 'Gérer les rôles et permissions', 'group' => 'Utilisateurs & rôles'],

    'manage settings' => ['label' => 'Gérer les paramètres', 'group' => 'Paramètres'],

];
# Plan : Systeme de facturation Labo 4M

## Contexte

- **Type** : Laboratoire medical/clinique — Republique du Congo
- **Framework** : Laravel 13 + Breeze (Blade) + Tailwind + Alpine.js
- **Permissions** : Spatie Laravel Permission v8.3 (deja installe)
- **Roles** : admin, editor, user
- **Certification** : SFEC (Systeme de Facturation Electronique Certifie) — API REST
- **Devise** : XAF (Franc CFA)

---

## 1. Architecture des clients

```
CLIENTS
├── ASSURER     Societes d'assurance (SGAM, NASSIMA...)
│               Paient leur % de couverture sur les factures
│
└── PARTICULIER Tout le reste
    ├── Entreprise  (avec company_name, NIF, RCS)
    └── Individu    (sans infos entreprise)
    Payent directement la totalite
```

---

## 2. Flux de facturation

### Flux ASSURE (1 facture avec split)

```
Employe (particulier) -> Labo 4M -> Examens realises
                              |
                    1 facture avec :
                    +-- Assurance paie: 80% (virement)
                    +-- Patient paie: 20% (cash/carte)
```

### Flux NON ASSURE (1 facture directe)

```
Client particulier -> Labo 4M -> Examens realises
                              |
                    1 facture -> Tout au client
```

### Flux SFEC (certification obligatoire)

```
1. Utilisateur cree facture -> status = 'brouillon'
2. Utilisateur clique "Certifier SFEC"
3. Systeme prepare le JSON pour l'API SFEC
4. Appel POST /api/v1/invoices
5. SFEC retourne: certification_number, signature, qr_code
6. Systeme stocke les donnees SFEC dans la facture
7. Status = 'envoyee'
8. Facture imprimable avec QR Code SFEC
```

---

## 3. Tables BDD (15 tables)

### Tables existantes (deja migrees)

| Table | Source |
|-------|--------|
| `users` | Breeze default |
| `roles` | Spatie |
| `permissions` | Spatie |
| `model_has_roles` | Spatie |
| `model_has_permissions` | Spatie |
| `role_has_permissions` | Spatie |
| `cache` | Laravel default |
| `jobs` | Laravel default |
| `sessions` | Laravel default |
| `password_reset_tokens` | Laravel default |

### Tables a creer (13)

---

### `company_settings` (singleton — enete facture)

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `name` | string | Nom du laboratoire |
| `niu` | string | **NIU** (Numero d'Identification Unique) — obligatoire SFEC |
| `logo` | string, nullable | Chemin vers le logo |
| `address` | text | Adresse |
| `phone` | string | Telephone |
| `email` | string | Email |
| `nif` | string | NIF |
| `rc` | string | Registre de commerce |
| `patente` | string, nullable | Patente |
| `cnss` | string, nullable | CNSS |
| `bank_name` | string, nullable | Banque |
| `bank_rib` | string, nullable | RIB |
| `sfec_api_key` | string, nullable | Cle API SFEC (production) |
| `sfec_api_key_sandbox` | string, nullable | Cle API SFEC (sandbox) |
| `sfec_environment` | enum(`production`, `sandbox`), default `sandbox` | Environnement SFEC |
| `created_at` / `updated_at` | timestamps | |

---

### `service_categories`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `name` | string | Biochimie, Microbiologie, Hematologie... |
| `description` | text, nullable | |
| `created_at` / `updated_at` | timestamps | |

---

### `services` (catalogue des examens)

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `category_id` | FK -> service_categories | |
| `name` | string | Libelle de l'examen |
| `code` | string, nullable | Code interne |
| `classification_code` | string, nullable | Code classification SFEC (C01, S01...) |
| `price` | decimal(12,2) | Prix unitaire |
| `description` | text, nullable | |
| `is_active` | boolean, default true | |
| `created_at` / `updated_at` | timestamps | |

---

### `insurers` (societes d'assurance)

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `name` | string | SGAM, NASSIMA, FENALCO... |
| `address` | text, nullable | |
| `phone` | string, nullable | |
| `email` | string, nullable | |
| `contact_name` | string, nullable | |
| `created_at` / `updated_at` | timestamps | |

---

### `clients`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `type` | enum(`assurer`, `particulier`) | Type de client |
| `name` | string | Nom / Raison sociale |
| `email` | string, nullable | |
| `phone` | string | Telephone |
| `address` | text, nullable | Adresse |
| `city` | string, nullable | Ville |
| `company_name` | string, nullable | Nom entreprise (si type=particulier entreprise) |
| `company_nif` | string, nullable | NIF entreprise |
| `company_rcs` | string, nullable | RCS entreprise |
| `niu` | string, nullable | NIU client (16-17 caractere si business) — SFEC |
| `rccm` | string, nullable | Numero RCCM — SFEC |
| `is_taxable` | boolean, default true | Assujetti a la TVA — SFEC |
| `contact_name` | string, nullable | Nom du contact |
| `contact_phone` | string, nullable | Tel. du contact |
| `notes` | text, nullable | Notes internes |
| `created_at` / `updated_at` | timestamps | |

---

### `agents` (employes d'une entreprise)

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `client_id` | FK -> clients | L'entreprise qui emploie l'agent |
| `name` | string | Nom de l'agent |
| `email` | string, nullable | |
| `phone` | string, nullable | |
| `matricule` | string, nullable | Matricule employe |
| `created_at` / `updated_at` | timestamps | |

---

### `insurance_contracts` (lien particulier <-> assureur)

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `client_id` | FK -> clients (particulier) | L'employe/entreprise assure |
| `insurer_id` | FK -> insurers | L'assureur |
| `contract_number` | string, unique | Numero de contrat (ex: CTR-2026-0001) |
| `coverage_rate` | decimal(5,2) | % couvert par l'assurance (ex: 80.00) |
| `start_date` | date | Debut contrat |
| `end_date` | date, nullable | Fin contrat (null = illimite) |
| `is_active` | boolean, default true | |
| `created_at` / `updated_at` | timestamps | |

---

### `invoices`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `client_id` | FK -> clients | Le patient/client |
| `agent_id` | FK -> agents, nullable | Si entreprise |
| `number` | string, unique | N facture (ex: `F-4M-2026-000001`) |
| `voucher_number` | string, nullable, unique | N Bon (ex: `BON-2026-000001`, si non-assure) |
| `date` | date | Date d'emission |
| `due_date` | date | Date d'echeance |
| `status` | enum(`brouillon`, `envoyee`, `payee`, `partiel`, `annulee`) | Statut |
| `insurance_contract_id` | FK -> insurance_contracts, nullable | Si assure |
| `pec_number` | string, nullable | N PEC / N.P.E.C |
| `recipient_type` | enum(`business`, `individual`, `government`, `foreign`) | Type destinataire SFEC |
| `currency` | enum(`XAF`, `USD`), default `XAF` | Devise |
| `subtotal` | decimal(12,2) | Sous-total HT |
| `tax_rate` | decimal(5,2) | Taux TVA (ex: 18.00) |
| `tax_amount` | decimal(12,2) | Montant TVA |
| `discount_type` | enum(`aucun`, `pourcentage`, `montant`) | Remise globale |
| `discount_value` | decimal(12,2), default 0 | Valeur remise |
| `discount_amount` | decimal(12,2), default 0 | Montant remise calcule |
| `total` | decimal(12,2) | Total TTC |
| `insurance_covered` | decimal(12,2), default 0 | Montant assurance |
| `patient_amount` | decimal(12,2), default 0 | Ticket moderateur |
| `paid_amount` | decimal(12,2), default 0 | Deja paye |
| `notes` | text, nullable | Observations |
| `created_by` | FK -> users | Qui a cree la facture |
| `sfec_certified` | boolean, default false | Facture certifiee SFEC ? |
| `sfec_certification_number` | string, nullable | N certification SFEC |
| `sfec_signature` | text, nullable | Signature cryptographique SFEC |
| `sfec_short_signature` | string, nullable | Version courte signature |
| `sfec_qr_code` | text, nullable | QR Code base64 SFEC |
| `sfec_certification_date` | datetime, nullable | Date certification SFEC |
| `sfec_identifier` | string, nullable | Reference interne SFEC |
| `created_at` / `updated_at` | timestamps | |

---

### `invoice_items`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `invoice_id` | FK -> invoices | |
| `service_id` | FK -> services, nullable | Lien catalogue examens |
| `description` | string | Libelle de la ligne |
| `type` | enum(`analyse`, `consultation`, `prelevement`, `frais`) | Type de prestation |
| `item_type` | enum(`produit`, `service`) | Type article SFEC |
| `quantity` | decimal(8,2) | Quantite |
| `unit_price` | decimal(12,2) | Prix unitaire HT |
| `discount_type` | enum(`aucun`, `pourcentage`, `montant`) | Remise ligne |
| `discount_value` | decimal(12,2), default 0 | Valeur remise |
| `discount_amount` | decimal(12,2), default 0 | Montant remise calcule |
| `net_amount` | decimal(12,2) | Montant net apres remise |
| `classification_code` | string, nullable | Code classification SFEC |
| `created_at` / `updated_at` | timestamps | |

---

### `payments`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `invoice_id` | FK -> invoices | |
| `amount` | decimal(12,2) | Montant paye |
| `payment_date` | date | Date du paiement |
| `method` | enum(`especes`, `virement`, `mobile_money`, `cheque`, `carte`) | Moyen de paiement |
| `reference` | string, nullable | Reference du paiement |
| `payer` | enum(`assurance`, `patient`) | Qui paie |
| `notes` | text, nullable | |
| `created_at` / `updated_at` | timestamps | |

---

### `visites` (visites medicales annuelles)

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `client_id` | FK -> clients | L'entreprise |
| `agent_id` | FK -> agents, nullable | L'employe concerne |
| `visit_date` | date | Date prevue / passee |
| `status` | enum(`planifiee`, `realisee`, `annulee`, `absent`) | Statut |
| `notes` | text, nullable | Observations |
| `reminder_sent` | boolean, default false | Rappel envoye ? |
| `created_at` / `updated_at` | timestamps | |

---

### `visit_exams` (pivot — examens realises lors d'une visite)

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `visit_id` | FK -> visites | |
| `service_id` | FK -> services | |
| `result` | text, nullable | Resultat de l'analyse |
| `created_at` | timestamp | |

---

### `reagents` (reactifs/consommables — stock basique)

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint PK | |
| `name` | string | Nom du reactif |
| `reference` | string, nullable | Reference fournisseur |
| `unit` | string | Unite (ml, g, kits...) |
| `quantity` | decimal(10,2) | Stock actuel |
| `min_quantity` | decimal(10,2) | Seuil minimum (alerte) |
| `max_quantity` | decimal(10,2), nullable | Stock maximum |
| `created_at` / `updated_at` | timestamps | |

---

## 4. Enums en francais

```php
// clients.type
'assurer', 'particulier'

// invoices.status
'brouillon', 'envoyee', 'payee', 'partiel', 'annulee'

// invoices.discount_type / invoice_items.discount_type
'aucun', 'pourcentage', 'montant'

// invoice_items.type (interne)
'analyse', 'consultation', 'prelevement', 'frais'

// invoice_items.item_type (SFEC)
'produit', 'service'

// invoices.recipient_type (SFEC)
'business', 'individual', 'government', 'foreign'

// invoices.currency
'XAF', 'USD'

// payments.method
'especes', 'virement', 'mobile_money', 'cheque', 'carte'

// payments.payer
'assurance', 'patient'

// visites.status
'planifiee', 'realisee', 'annulee', 'absent'

// company_settings.sfec_environment
'production', 'sandbox'
```

---

## 5. Mapping paiement -> SFEC

| Notre enum | SFEC payment_method |
|------------|---------------------|
| `especes` | `cash` |
| `virement` | `bank_transfer` |
| `mobile_money` | `mobile_money` |
| `cheque` | `cheque` |
| `carte` | `card` |

---

## 6. TVA Congo

| Taux | Code | Description |
|------|------|-------------|
| 18% | T | Taux normal |
| 5% | R | Taux reduit |
| 0% | — | Exonere |

---

## 7. Modèles Eloquent (14)

| Model | Relations |
|-------|-----------|
| `CompanySetting` | — (singleton) |
| `ServiceCategory` | hasMany(Service) |
| `Service` | belongsTo(ServiceCategory) |
| `Insurer` | hasMany(InsuranceContract) |
| `InsuranceContract` | belongsTo(Client), belongsTo(Insurer) |
| `Client` | hasMany(Agent), hasMany(Invoice), hasMany(InsuranceContract) |
| `Agent` | belongsTo(Client), hasMany(Invoice), hasMany(Visit) |
| `Invoice` | belongsTo(Client), belongsTo(Agent), belongsTo(InsuranceContract), hasMany(InvoiceItem), hasMany(Payment) |
| `InvoiceItem` | belongsTo(Invoice), belongsTo(Service) |
| `Payment` | belongsTo(Invoice) |
| `Visit` | belongsTo(Client), belongsTo(Agent), belongsToMany(Service via visit_exams) |
| `Reagent` | — |

Le `User` model existant garde `created_by` sur les factures (qui a cree).

---

## 8. Controllers (8)

| Controller | Actions |
|------------|---------|
| `ClientController` | index, create, store, show, edit, update, destroy |
| `InvoiceController` | index, create, store, show, edit, update, destroy, print, updateStatus, certify (SFEC) |
| `PaymentController` | store, destroy (nested under invoice) |
| `VisitController` | index, create, store, show, edit, update, destroy, calendar |
| `ServiceController` | index, create, store, edit, update, destroy |
| `InsurerController` | index, create, store, edit, update, destroy |
| `ReagentController` | index, create, store, edit, update, destroy |
| `CompanySettingController` | edit, update |

---

## 9. Vues Blade

| Vue | Description |
|-----|-------------|
| `dashboard` | Stats CA, factures en attente, nb clients, alertes stock |
| `clients/index` | Liste (filtre assurer/particulier) |
| `clients/create` | Formulaire creation |
| `clients/edit` | Formulaire modification |
| `clients/show` | Detail client + agents + factures |
| `invoices/index` | Liste (filtres statut, date, client, type) |
| `invoices/create` | Formulaire creation (lignes dynamiques) |
| `invoices/show` | Detail facture + paiements + bouton imprimer + statut SFEC |
| `invoices/edit` | Modification (si brouillon) |
| `invoices/print` | Version imprimable SFEC avec QR Code |
| `visits/index` | Liste visites |
| `visits/create` | Formulaire creation |
| `visits/edit` | Formulaire modification |
| `visits/show` | Detail visite + examens |
| `visits/calendar` | Vue calendrier |
| `services/index` | Catalogue exams |
| `services/create` | Ajout examen |
| `services/edit` | Modification examen |
| `insurers/index` | Liste assureurs |
| `insurers/create` | Ajout assureur |
| `insurers/edit` | Modification assureur |
| `reagents/index` | Stock reactifs |
| `reagents/create` | Ajout reactif |
| `reagents/edit` | Modification reactif |
| `settings/edit` | Company settings + parametres SFEC |

---

## 10. Routes (protegees par auth + roles)

```php
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', ...)->name('dashboard');

    // Clients (admin + editor)
    Route::middleware('role:admin,editor')
        ->prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::get('/create', [ClientController::class, 'create'])->name('create');
            Route::post('/', [ClientController::class, 'store'])->name('store');
            Route::get('/{client}', [ClientController::class, 'show'])->name('show');
            Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [ClientController::class, 'update'])->name('update');
            Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
        });

    // Factures (admin + editor)
    Route::middleware('role:admin,editor')
        ->prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/create', [InvoiceController::class, 'create'])->name('create');
            Route::post('/', [InvoiceController::class, 'store'])->name('store');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
            Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
            Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
            Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
            Route::get('/{invoice}/print', [InvoiceController::class, 'print'])->name('print');
            Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('status');
            Route::post('/{invoice}/certify', [InvoiceController::class, 'certify'])->name('certify');
        });

    // Paiements (admin + editor)
    Route::middleware('role:admin,editor')
        ->prefix('invoices/{invoice}/payments')->name('payments.')->group(function () {
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
        });

    // Visites (admin + editor)
    Route::middleware('role:admin,editor')
        ->prefix('visits')->name('visits.')->group(function () {
            Route::get('/', [VisitController::class, 'index'])->name('index');
            Route::get('/calendar', [VisitController::class, 'calendar'])->name('calendar');
            Route::get('/create', [VisitController::class, 'create'])->name('create');
            Route::post('/', [VisitController::class, 'store'])->name('store');
            Route::get('/{visit}', [VisitController::class, 'show'])->name('show');
            Route::get('/{visit}/edit', [VisitController::class, 'edit'])->name('edit');
            Route::put('/{visit}', [VisitController::class, 'update'])->name('update');
            Route::delete('/{visit}', [VisitController::class, 'destroy'])->name('destroy');
        });

    // Services (admin + editor)
    Route::middleware('role:admin,editor')
        ->prefix('services')->name('services.')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index');
            Route::get('/create', [ServiceController::class, 'create'])->name('create');
            Route::post('/', [ServiceController::class, 'store'])->name('store');
            Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
            Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
            Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
        });

    // Assureurs (admin + editor)
    Route::middleware('role:admin,editor')
        ->prefix('insurers')->name('insurers.')->group(function () {
            Route::get('/', [InsurerController::class, 'index'])->name('index');
            Route::get('/create', [InsurerController::class, 'create'])->name('create');
            Route::post('/', [InsurerController::class, 'store'])->name('store');
            Route::get('/{insurer}/edit', [InsurerController::class, 'edit'])->name('edit');
            Route::put('/{insurer}', [InsurerController::class, 'update'])->name('update');
            Route::delete('/{insurer}', [InsurerController::class, 'destroy'])->name('destroy');
        });

    // Reactifs (admin + editor)
    Route::middleware('role:admin,editor')
        ->prefix('reagents')->name('reagents.')->group(function () {
            Route::get('/', [ReagentController::class, 'index'])->name('index');
            Route::get('/create', [ReagentController::class, 'create'])->name('create');
            Route::post('/', [ReagentController::class, 'store'])->name('store');
            Route::get('/{reagent}/edit', [ReagentController::class, 'edit'])->name('edit');
            Route::put('/{reagent}', [ReagentController::class, 'update'])->name('update');
            Route::delete('/{reagent}', [ReagentController::class, 'destroy'])->name('destroy');
        });

    // Parametres (admin only)
    Route::middleware('role:admin')
        ->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [CompanySettingController::class, 'edit'])->name('edit');
            Route::put('/', [CompanySettingController::class, 'update'])->name('update');
        });
});
```

---

## 11. Permissions Spatie (20)

| Permission | Description |
|------------|-------------|
| `view clients` | Voir la liste des clients |
| `create client` | Creer un client |
| `edit client` | Modifier un client |
| `delete client` | Supprimer un client |
| `view invoices` | Voir les factures |
| `create invoice` | Creer une facture |
| `edit invoice` | Modifier une facture |
| `delete invoice` | Supprimer une facture |
| `manage payments` | Gerer les paiements |
| `print invoice` | Imprimer/certifier une facture |
| `view visits` | Voir les visites |
| `create visit` | Creer une visite |
| `edit visit` | Modifier une visite |
| `delete visit` | Supprimer une visite |
| `manage services` | Gérer le catalogue d'examens |
| `manage reagents` | Gerer le stock de reactifs |
| `manage insurers` | Gerer les assureurs |
| `manage settings` | Gerer les parametres |
| `view dashboard` | Acceder au tableau de bord |
| `manage users` | Gerer les utilisateurs |

---

## 12. Numerotation auto

| Sequence | Format | Exemple |
|----------|--------|---------|
| Facture SFEC | `F-4M-{ANNEE}-{6 chiffres}` | `F-4M-2026-000001` |
| Bon de commande | `BON-{ANNEE}-{6 chiffres}` | `BON-2026-000001` |
| PEC | `PEC-{ANNEE}-{6 chiffres}` | `PEC-2026-000001` |
| Contrat assurance | `CTR-{ANNEE}-{4 chiffres}` | `CTR-2026-0001` |

Auto-genere au moment de la creation via un boot() dans le model.

---

## 13. Integration API SFEC

### Environnements

| Service | Production | Sandbox |
|---------|-----------|---------|
| API SFEC | `https://api.sfec.gouv.cg` | `https://sandbox.api.sfec.gouv.cg` |
| Portail e-Facture | `https://efacture.gouv.cg` | `https://sandbox.efacture.gouv.cg` |

### Authentification

- Header: `X-API-Key: {cle_api}`
- Cle liee a l'entreprise

### Endpoint certification

- `POST /api/v1/invoices`

### Champs envoyes a SFEC

| SFEC API | Notre champ | Table |
|----------|-------------|-------|
| `invoice_id` | `number` | invoices |
| `invoice_type` | — | Toujours `salesInvoice` |
| `taxpayer_niu` | `niu` | company_settings |
| `recipient_type` | `recipient_type` | invoices |
| `recipient_name` | `name` | clients |
| `recipient_niu` | `niu` | clients |
| `recipient_rccm` | `rccm` | clients |
| `recipient_address` | `address` | clients |
| `recipient_phone` | `phone` | clients |
| `recipient_email` | `email` | clients |
| `is_recipient_taxable` | `is_taxable` | clients |
| `subtotal` | `subtotal` | invoices |
| `total_tax_t_amount` | — | Calcule: TVA 18% |
| `total_tax_r_amount` | — | Calcule: TVA 5% |
| `total_exempt_amount` | — | Calcule: TVA 0% |
| `total_tax_amount` | `tax_amount` | invoices |
| `discount_amount` | `discount_amount` | invoices |
| `total_line_discount_amount` | — | Somme remises lignes |
| `additional_cent_tax` | — | Toujours 0 |
| `electronic_stamp_duty` | — | Toujours 0 |
| `total_amount` | `total` | invoices |
| `amount_due` | `total - paid_amount` | invoices |
| `currency` | `currency` | invoices |
| `payment_method` | `method` | payments (apres mapping) |
| `items[].designation` | `description` | invoice_items |
| `items[].classification_code` | `classification_code` | invoice_items |
| `items[].type` | `item_type` | invoice_items |
| `items[].unit_price` | `unit_price` | invoice_items |
| `items[].quantity` | `quantity` | invoice_items |
| `items[].subtotal` | `quantity * unit_price` | Calcule |
| `items[].discount_amount` | `discount_amount` | invoice_items |
| `items[].discount_type` | `discount_type` | invoice_items |
| `items[].net_amount` | `net_amount` | invoice_items |
| `items[].tax_rate` | — | Selon taux TVA |
| `items[].tax_amount` | — | Calcule |
| `items[].total_amount` | — | Calcule |

### Reponse SFEC -> Stockage

| SFEC Response | Notre champ | Type |
|---------------|-------------|------|
| `certification_number` | `sfec_certification_number` | string |
| `signature` | `sfec_signature` | text |
| `short_signature` | `sfec_short_signature` | string |
| `qr_code` | `sfec_qr_code` | text (base64) |
| `certification_date` | `sfec_certification_date` | datetime |
| `identifier` | `sfec_identifier` | string |
| `invoice_number` | `number` | string |

### Codes d'erreur SFEC

| Code | Description |
|------|-------------|
| 400 | Donnees invalides |
| 401 | Cle API invalide |
| 422 | Erreur validation metier |
| 409 | Facture deja certifiee |
| 500 | Erreur interne |

---

## 14. Seeds de test

- Company settings (Labo 4M)
- 4-5 service categories (Biochimie, Microbiologie, Hematologie, Parasitologie, Imagerie)
- 10-15 services par categorie
- 3-4 assureurs (SGAM, NASSIMA, FENALCO, NSIA)
- 5-6 clients particuliers (2 entreprises + 2 individuels + 2 assures)
- 2-3 insurance contracts
- 5-6 agents rattaches aux entreprises
- 10-15 factures avec differents statuts
- Quelques paiements partiels
- 5-10 visites (planifiees + realisees)
- 10-15 reactifs/consommables

---

## 15. Chronophase du projet

### Phase 1 — Fondations (Semaines 1-2)

| Jour | Tache | Details |
|------|-------|---------|
| J1-J2 | Migrations | Creer les 13 nouvelles tables |
| J3-J4 | Models Eloquent | 12 models + relations + boot() numerotation |
| J5 | Seeds de base | Company settings, categories, services, assureurs |
| J6-J7 | Clients CRUD | Controller + vues (index, create, edit, show) |
| J8-J10 | Agents CRUD | Ajout/suppression d'agents sur client entreprise |

**Livrable** : Gestion des clients et agents fonctionnelle

### Phase 2 — Facturation (Semaines 3-4)

| Jour | Tache | Details |
|------|-------|---------|
| J11-J13 | Factures CRUD | Controller + vues (index, create, edit, show) |
| J14-J15 | Lignes dynamiques | Ajout/suppression de lignes de facture en JS |
| J16-J17 | Calculs auto | Sous-total, TVA, remises, total, ticket moderateur |
| J18-J19 | Paiements | PaymentController + vues (store/destroy) |
| J20 | Numerotation auto | Boot() F-4M-2026-000001 + BON-2026-000001 |

**Livrable** : Facturation complete avec split assurance/patient

### Phase 3 — Assurances (Semaine 5)

| Jour | Tache | Details |
|------|-------|---------|
| J21-J22 | Assureurs CRUD | Controller + vues |
| J23-J24 | Contrats assurance | CRUD + taux de couverture |
| J25 | Formulaire facture | Integration assureur + PEC dans creation facture |

**Livrable** : Gestion des assurances et contrats

### Phase 4 — SFEC (Semaine 6)

| Jour | Tache | Details |
|------|-------|---------|
| J26-J27 | Service API SFEC | Client HTTP + methode certify() |
| J28-J29 | Integration certification | Bouton "Certifier SFEC" + stockage reponse |
| J30 | Page impression | Facture avec QR Code + mentions SFEC |

**Livrable** : Certification SFEC fonctionnelle

### Phase 5 — Visites & Stock (Semaine 7)

| Jour | Tache | Details |
|------|-------|---------|
| J31-J32 | Visites CRUD | Controller + vues |
| J33 | Calendrier | Vue calendrier des visites |
| J34-J35 | Reactifs CRUD | Controller + vues + alertes stock bas |

**Livrable** : Gestion des visites et du stock

### Phase 6 — Dashboard & Finalisation (Semaine 8)

| Jour | Tache | Details |
|------|-------|---------|
| J36-J37 | Dashboard | Stats CA, factures en attente, nb clients, alertes stock |
| J38 | Permissions Spatie | Mise a jour seeder (20 permissions) |
| J39 | Navigation | Menu lateral complet avec tous les liens |
| J40 | Tests | Tests de base sur les controllers principaux |

**Livrable** : Application complete et fonctionnelle

---

## Resume des livrables

| Phase | Semaine | Livrable |
|-------|---------|----------|
| 1 | 1-2 | Clients + Agents CRUD |
| 2 | 3-4 | Factures + Paiements CRUD |
| 3 | 5 | Assureurs + Contrats |
| 4 | 6 | Certification SFEC |
| 5 | 7 | Visites + Stock |
| 6 | 8 | Dashboard + Permissions + Tests |

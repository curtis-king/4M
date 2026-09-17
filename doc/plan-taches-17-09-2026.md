# Plan d'exécution — Tâches du 17/09/2026

**Projet** : Labo 4M (Laravel 13, PHP 8.4, Blade + Alpine.js + Tailwind, Vite, SQLite)
**Auteur du plan** : session opencode du 17/09/2026
**Exécution** : messiasayi — ne pas modifier du code hors périmètre des fichiers listés ci-dessous ("ne modifie pas le code d'autrui").

> Règle d'or : une tâche = une branche git = un commit clair. Vérifier à chaque fin de tâche que l'app tourne toujours (`php artisan serve` + `npm run dev`).

Sommaire des tâches :
1. Retirer le lien « Register » de la page d'accueil
2. Embellir la page d'accueil
3. Logo dans l'onglet du navigateur (favicon)
4. Logo à la place de « 4M » dans la barre latérale
5. Cas de structure = validations des champs (input email, numéro, téléphone…)
6. NIU au format NIU (16-17 chiffres)
7. Module « Devis » complet (CRUD + impression + conversion en facture)

---

## 0. Prérequis communs

- PHP 8.4 actif : `php -v` → 8.4.x.
- Dépendances installées : `composer install`, `npm install` (déjà fait).
- Serveur : `php artisan serve` (lancement sur http://localhost:8000) + `npm run dev` pour Vite.
- Fichier logo reçu de messiasayi à placer ici : `public/img/logo.png` (et `public/img/logo.svg` si fourni). Créer le dossier `public/img/` si besoin.

---

## Tâche 1 — Retirer le lien « Register » de la page d'accueil

**Objectif** : sur `/`, l'utilisateur non connecté ne doit voir que le bouton « Log in », plus le lien « Register ».

**Fichier** : `resources/views/welcome.blade.php`

- Supprimer tout le bloc conditionnel `@if (Route::has('register')) … @endif` (lignes 39-45).
- Conserver le lien « Dashboard » pour les connectés et le lien « Log in » pour les invités.

**En option (sécurité)** : désactiver totalement l'inscription publique.
- `routes/auth.php` lignes 15-18 : retirer les routes `register` GET/POST.
- Supprimer le controller : `app/Http/Controllers/Auth/RegisteredUserController.php` + la vue `resources/views/auth/register.blade.php`.
- ⚠️ Ne le faire QUE si messiasayi confirme qu'on ne veut plus d'inscription publique.

**Recommandé pour l'instant** : seulement retirer le lien de la page d'accueil (périmètre demandé).

**Vérif** : `/` avec une session déconnectée → « Log in » seulement. `/register` directe → page inaccessible (ou toujours accessible si on n'a pas supprimé la route).

---

## Tâche 2 — Embellir la page d'accueil

**Fichier** : `resources/views/welcome.blade.php` (entièrement à retravailler — c'est la page Laravel par défaut)

**Périmètre** : garder la structure de base (head, `@fonts`, `@vite`) et remplacer le contenu.

Suggestions de sections (à adapter selon la charte graphique du labo, couleurs `primary-*` déjà présentes dans le thème) :

1. **Navbar** : logo (image `asset('img/logo.png')`) + « Labo 4M », lien « Log in » (bouton primaire) à droite ; le lien Register reste retiré (T1).
2. **Hero** : titre type « Votre laboratoire d'analyses, facturation simplifiée », sous-titre, bouton CTA « Se connecter » (`route('login')`).
3. **Section points forts** : 3-4 cartes (Facturation / Clients / Assureurs / Devis…) avec icônes SVG (mêmes icônes que `layouts/navigation.blade.php`).
4. **Footer** : nom du labo, contact (utilise `CompanySetting` si dispo via injecteur, sinon texte statique).

**Conventions** :
- Ne pas réinventer les couleurs : utilser `bg-primary-600`, `text-primary-700`, `bg-primary-50` (existent déjà dans le projet).
- Responsive mobile-first (Tailwind `sm:`, `lg:`, `max-w-*`).
- Dark mode optionnel conservé ou supprimé — au choix de messiasayi.

**Vérif** : `/` rend bien, pas d'erreur console (inspecter l'onglet Réseau), CTA pointe vers `/login`.

---

## Tâche 3 — Logo dans l'onglet du navigateur (favicon)

**Prérequis** : logo présent dans `public/img/logo.png` (voir section 0).

**Fichiers** :
- `resources/views/layouts/app.blade.php` (lignes 3-16, dans `<head>`)
- `resources/views/layouts/guest.blade.php` (lignes 3-16, dans `<head>`)
- `resources/views/welcome.blade.php` (lignes 3-8, dans `<head>`)

Ajouter dans les 3 `<head>` :
```html
<link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}" />
<link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}" />
```
Remplacer/ajouter aussi le `public/favicon.ico` actuel par une version en `favicon.ico` du logo (ou le laisser tel quel — le `<link>` PNG prend le dessus sur les navigateurs modernes).

**Vérif** : ouvrir `/login` et `/`, l'onglet du navigateur affiche le logo ; `Ctrl+Shift+R` (vidage cache).

---

## Tâche 4 — Logo à la place de « 4M » dans la barre latérale

**Fichier** : `resources/views/layouts/navigation.blade.php`

- Ligne 12 : remplacer le bloc :
  ```html
  <div class="flex h-9 w-9 ... text-white">4M</div>
  ```
  par :
  ```html
  <img src="{{ asset('img/logo.png') }}" alt="Labo 4M"
       class="h-9 w-9 shrink-0 rounded-xl object-contain" />
  ```
- Conserver le texte « Labo 4M » (ligne 14) et « Facturation » (ligne 15) tels quels.
- **En bonus (cohérence brand)** : remplacer aussi le logo générique des pages auth :
  - `resources/views/layouts/guest.blade.php` ligne 21 (`<x-application-logo .../>`) par `<img src="{{ asset('img/logo.png') }}" class="h-20 w-20 object-contain" />`.

**Vérif** : sidebar large (72) et réduite (20) → le logo s'affiche correctement dans les deux états (moins de contenu quand réduit, ok car `<img>` sans dépendre du `:class` de texte, seul le texte disparaît).

---

## Tâche 5 — Cas de structure : validation des champs

**Objectif** : chaque champ doit respecter son type/format (email → email, téléphone → chiffres, NIU → chiffres, montants → numériques…), en backend ET en frontend.

### 5.1 Champ email
Déjà correct sur `ClientController` (`nullable|email|max:255`) et `CompanySettingController` (`required|email|max:255`).
- **Auditer** les autres controllers et ajouter `email` si manquant (ex. `InsurerController`, `ReagentController`, `ServiceController`, `VisitController`).
- Frontend : ajouter `type="email"` sur tous les `<input name="email">` (rechercher dans `resources/views/`).

### 5.2 Champ téléphone (numéros uniquement)
Déjà : `phone => ...|string|max:20` dans `ClientController` (lignes 56, 116).
**Renforcer** :
```php
'phone' => 'required|regex:/^[0-9+][0-9 .-]{7,19}$/|max:20',
```
et `contact_phone` idem en nullable.
- Frontend : `type="tel" inputmode="tel"` + `pattern="[0-9+]{8,20}"`.
- Appliquer la même règle à `CompanySettingController::phone`, et aux phones dans `Agent`, `Insurer` (vues `assures` et autres).

### 5.3 Montants / quantités (chiffres uniquement)
- Contrôler que tous les `quantity`, `unit_price`, `discount_value`, `coverage_rate`, `tax_rate`, `paid_amount`, `amount` ont des règles `numeric` (ou `integer`). Vérifier `InvoiceController` (lignes ~179, 274), `PaymentController`, `StatementController` (déjà `integer` pour `visit_ids.*`).
- Frontend : `type="number" step="0.01" min="0" inputmode="decimal"` sur tous les inputs de montants.

### 5.4 Champs dates
- `type="date"` + règle `date`/`after_or_equal` si pertinent (déjà `required|date` sur `InvoiceController`).

### 5.5 Tableau de contrôle (à faire passer en revue un par un)

| Formulaire | Fichier vue | Champs à contrôler | Règle actuelle | Recommandation |
|---|---|---|---|---|
| Client | `clients/create|edit.blade.php` | email, phone, contact_phone, niu, rccm, nif, rcs, coverage_rate | `string`, `email` | 5.1 / 5.2 / T6 |
| Société | `settings/edit.blade.php` | niu, nif, rc, phone, email | `string`, `email` | 5.1 / 5.2 / T6 |
| Assureurs | `insurers/*` | phone, email, taux | `string` | 5.1 / 5.2 |
| Facture | `invoices/create|edit.blade.php` | quantités, prix, taxes, remises, dates | `numeric`, `integer` | 5.3 / 5.4 |
| Paiement | `invoices/show|payments` | amount | — | 5.3 |
| Visites | `visits/create|edit.blade.php` | dates, nombres | — | 5.3 / 5.4 |
| Services/Réactifs | `services/*`, `reagents/*` | prix, quantités | — | 5.3 |

**Vérif** : soumettre un formulaire client avec « abc » dans téléphone → erreur affichée côté serveur ET blocage frontend ; messages d'erreur en français (`withErrors` déjà gérées via `<x-input-error>`).

---

## Tâche 6 — NIU au format NIU (16-17 chiffres)

Rappel : format NIU au Congo = **16 ou 17 caractères, commençant par « M » ou « P », alphanumérique** (contient aussi des lettres).

### 6.1 Normalisation dans le modèle
`app/Models/Client.php` + `app/Models/CompanySetting.php` : mutateur qui met le NIU en majuscules au stockage :
```php
public function setNiuAttribute($value): void
{
    $this->attributes['niu'] = $value === null ? null : strtoupper(trim((string) $value));
}
```

### 6.2 Règle custom NIU
Créer `app/Rules/NiuRule.php` (déjà en place) :
```php
if (preg_match('/^[MP][A-Z0-9]{15,16}$/', strtoupper(trim((string) $value))) !== 1) {
    $fail('Le NIU doit contenir 16 ou 17 caractères alphanumériques et commencer par « M » ou « P ».');
}
```

Application dans :
- `app/Http/Controllers/ClientController.php` (store + update) : `'niu' => ['nullable', 'string', new NiuRule]`
- `app/Http/Controllers/CompanySettingController.php` : `'niu' => ['required', 'string', new NiuRule]`

### 6.3 `SfecService::normalizeNiu`
Valider le même format (majuscules + regex `^[MP][A-Z0-9]{15,16}$`) au lieu de la vérification « 16 ou 17 chiffres ».

### 6.4 Format frontend
Dans `clients/create.blade.php`, `clients/edit.blade.php` et `settings/edit.blade.php` :
```html
<input ... maxlength="17" pattern="[MP][A-Za-z0-9]{15,16}" inputmode="text"
       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
```

**Vérif** : `M` ou `P` en premier, 16-17 caractères au total → OK ; première lettre autre que M/P ou mauvaise longueur → erreur de validation ; stockage en majuscules.

---

## Tâche 7 — Module « Devis » complet

**Objectif** : CDRU devis (list / create / show / edit / print / delete) + conversion devis → facture + statuts.

Le module `invoices` sert de modèle complet (controller, model, routes, vues, modèle d'items). On va le dupliquer/ajuster.

### 7.1 Base de données
Créer 2 migrations (en reprenant le schéma `invoices` et `invoice_items`) :
- `2026_09_17_000010_create_devis_table.php`
- `2026_09_17_000011_create_devis_items_table.php`

**Table `devis`** (colonnes calquées sur `invoices`, adaptées — PAS de SFEC/paiements) :
`id, number, client_id (nullable FK), walk_in_name (nullable), agent_id (nullable FK), date, due_date (validité), status (brouillon|envoye|accepte|refuse|converti), currency, subtotal, tax_rate, tax_amount, discount_type, discount_value, discount_amount, total, notes, created_by (FK users), invoice_id (nullable FK → facture issue de la conversion), timestamps`

**Table `devis_items`** :
`id, devis_id (FK cascade), service_id (nullable FK), description, type (analyse|consultation|prelevement|frais), quantity, unit_price, discount_type, discount_value, discount_amount, net_amount, timestamps`

### 7.2 Modèles
- `app/Models/Devis.php` : fillable + casts (dates, decimals) + relations `client()`, `agent()`, `items()`, `creator()`, `invoice()` + `recalculate()` (copie simple : `subtotal = sum(net_amount)`, remise, taxe, `total`) + numérotation automatique `D-4M-YYYY-%06d` (imiter `Invoice::booted()`) + badge de statut.
- `app/Models/DevisItem.php` : fillable + casts + hook `saving` qui calcule `discount_amount` et `net_amount` (imiter `InvoiceItem::booted()`).

### 7.3 Controller + routes
- `app/Http/Controllers/DevisController.php` : `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `print`, `convert`.
  - `convert(Devis $devis)` : crée une `Invoice` depuis le devis (mêmes champs + items), numéro F-4M, relie `devis->invoice_id`, passe le devis en `converti`, redirige vers la facture.
- `routes/web.php` : ajouter dans le groupe `auth` (avant le wildcard si besoin), sous `prefix('devis')->name('devis.')` :
```php
Route::middleware('role:admin,editor')->prefix('devis')->name('devis.')->group(function () {
    Route::get('/', [DevisController::class, 'index'])->name('index');
    Route::get('/create', [DevisController::class, 'create'])->name('create');
    Route::post('/', [DevisController::class, 'store'])->name('store');
    Route::get('/{devis}', [DevisController::class, 'show'])->name('show');
    Route::get('/{devis}/edit', [DevisController::class, 'edit'])->name('edit');
    Route::put('/{devis}', [DevisController::class, 'update'])->name('update');
    Route::delete('/{devis}', [DevisController::class, 'destroy'])->name('destroy');
    Route::get('/{devis}/print', [DevisController::class, 'print'])->name('print');
    Route::post('/{devis}/convert', [DevisController::class, 'convert'])->name('convert');
});
```
Exécuter ensuite `php artisan route:list | grep devis` pour vérifier.

### 7.4 Permission « devis »
- `database/seeders/RolesAndPermissionsSeeder.php` :
  - Ajouter aux permissions : `view devis`, `create devis`, `edit devis`, `delete devis`, `print devis`, `convert devis`.
  - ⚠️ Le seeder utilise `create()` → **le relancer en l'état plantera** (données en double). Le rendre idempotent : remplacer les `Permission::create`/`Role::create` par `firstOrCreate` + `syncPermissions`.
- Relancer ensuite : `php artisan db:seed --class=RolesAndPermissionsSeeder`.
- Redonner les permissions à ton utilisateur (**rôle admin** reçoit tout via le seeder ; vérifier la réassignation) :
  `php artisan tinker --execute="\$u = App\Models\User::where('email','ton@email')->first(); \$u->syncRoles(Spatie\Permission\Models\Role::all()); \
  \$u->syncPermissions(Spatie\Permission\Models\Permission::all()); echo \$u->can('create devis') ? 'OK' : 'KO';"`

### 7.5 Vues (imiter `resources/views/invoices/`)
Créer `resources/views/devis/` :
- `index.blade.php` : tableau (n°, client, date, validité, total, statut avec badge, actions show/edit/print/delete/convert), barre de recherche + filtre par statut (copier `invoices/index.blade.php`).
- `create.blade.php` / `edit.blade.php` : formulaire client (select clients + walk-in), lignes d'items dynamiques Alpine (quantité, prix, remise, total ligne), total calculé (imiter exactement `invoices/create.blade.php`).
- `show.blade.php` : récap devis + boutons « Imprimer » et « Convertir en facture ».
- `print.blade.php` : version imprimable HTML (imiter `invoices/print.blade.php`, sans blocs SFEC, mention « DEVIS » et validité au lieu d'échéance).

### 7.6 Menu latéral
`resources/views/layouts/navigation.blade.php` : ajouter un lien « Devis » (même gabarit que « Factures », lignes 40-49) après « Factures », gardé sous `@canany(['view devis','create devis'])`, icône SVG document.

### 7.7 Dashboard (option bonus)
`InvoiceController::dashboard()` — ajouter un petit compteur « Devis en attente » (statut `envoye|accepte`) en réutilisant le même style de carte. **Uniquement si messiasayi le demande** (hors périmètre strict).

### 7.8 Vérifs
- `php artisan migrate`
- `php artisan test` (si des tests existent — sinon exécuter à la main) :
  1. Créer un devis avec 2 items → total correct (remise % et montant).
  2. Modifier un item → recalcul.
  3. Imprimer → la vue print s'ouvre.
  4. Convertir → une facture F-4M-... est créée avec les mêmes items, le devis passe en « converti », bouton converti inactif/désactivé.
  5. Un utilisateur `user` ne voit pas le menu Devis (permission).

---

## Récap des fichiers touchés par tâche

| Tâche | Fichiers modifiés | Nouveaux fichiers |
|---|---|---|
| 1 | `resources/views/welcome.blade.php` | — |
| 2 | `resources/views/welcome.blade.php` | — |
| 3 | `layouts/app.blade.php`, `layouts/guest.blade.php`, `welcome.blade.php`, `public/favicon.ico` | `public/img/logo.png` |
| 4 | `layouts/navigation.blade.php`, `layouts/guest.blade.php` | — |
| 5 | controllers (Client, CompanySetting, Insurer, Invoice, Payment…), vues formulaire | (évent. `app/Rules/`) |
| 6 | `app/Models/Client.php`, `app/Models/CompanySetting.php`, `ClientController`, `CompanySettingController`, vues clients/settings | `app/Rules/NiuRule.php` |
| 7 | `routes/web.php`, `RolesAndPermissionsSeeder`, `layouts/navigation.blade.php` | migrations `devis*`, `app/Models/Devis.php` + `DevisItem.php`, `app/Http/Controllers/DevisController.php`, `resources/views/devis/*` |

## Ordre d'exécution conseillé
1 → 3 → 4 → 6 → 5 → 7 (le 6 avant le 5 car il introduit la règle NIU), puis test global.
2 (embellissement) peut se faire en parallèle / dernier.

## Rappels finaux
- Toujours `curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/` après chaque tâche → 200.
- Lancer `php artisan config:clear` si erreur de config.
- Commiter par tâche (messages fr explicites, ne pas stager les fichiers hors périmètre).
# Audit complet du projet ARG

> Analyse effectuee le 2026-03-05 — Projet : plateforme web type GitHub pour un jeu ARG (Alternate Reality Game)

---

## Vue d'ensemble

Le projet est une application web PHP simulant une plateforme GitHub, servant de support a un jeu ARG. Les joueurs debloquent des personnages via des codes secrets, accedent a des repositories caches, et progressent dans une histoire interactive.

**Stack technique** : PHP procedural, MySQL (PDO), CSS custom, HTML/JS vanilla
**Taille** : ~2700 lignes PHP, 16 fichiers PHP, 3 mini-jeux HTML, 1 feuille CSS, 1 schema SQL

---

## Ce qui va bien

### Securite (les bons points)

| Element | Detail |
|---------|--------|
| **Hachage des mots de passe** | `password_hash(PASSWORD_DEFAULT)` et `password_verify()` utilises correctement (`register.php:29`, `login.php:23`) |
| **Requetes preparees PDO** | Toutes les requetes SQL utilisent des placeholders `?` — aucune interpolation directe dans les clauses WHERE |
| **Echappement HTML** | `htmlspecialchars()` utilise de maniere coherente pour les sorties, `nl2br()` applique apres l'echappement |
| **Messages d'erreur generiques** | `login.php:29` : "Invalid username or password" — ne revele pas si c'est le login ou le mot de passe qui est faux |
| **Timeout de session** | `config.php:107-112` : timeout d'inactivite de 1 heure |

### Code et architecture (les bons points)

| Element | Detail |
|---------|--------|
| **CSS bien structure** | `github-style.css` : variables CSS pour le theming (lignes 2-23), nommage BEM-like coherent |
| **Schema SQL normalise** | 3NF respecte, cles etrangeres avec CASCADE, contraintes d'unicite sur les champs critiques |
| **Encodage UTF-8** | `utf8mb4` partout dans la base de donnees |
| **Mecaniques ARG** | Systeme de progression bien pense : deblocage par code secret, suivi du progres, controle d'acces au contenu verrouille |
| **`urlencode()` dans dashboard** | Les liens dynamiques dans `dashboard.php` utilisent correctement `urlencode()` |

---

## Ce qui ne va pas

### CRITIQUE — Securite

#### 1. Aucune protection CSRF
**Fichiers concernes** : `login.php:60`, `register.php:77`, `repo.php:278`, `messages.php`
Aucun formulaire POST ne contient de token CSRF. Un attaquant peut forger des requetes depuis un site externe.

**Correctif** : Generer un token en session et le verifier a chaque soumission de formulaire.

#### 2. Injection SQL dans `check-db.php:11`
```php
$stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
```
Le nom de table n'est pas parametre. Meme si la liste vient d'un tableau hardcode, c'est un pattern dangereux.

#### 3. Redirection ouverte dans `config.php:55`
```php
header("Location: $url");
```
La fonction `redirect()` n'effectue aucune validation de l'URL. Utilisee dans `login.php:26`, `register.php:47`, etc.

**Correctif** : Valider que l'URL est relative ou appartient au domaine.

#### 4. Absence totale de headers de securite
Aucun des headers suivants n'est defini :
- `Content-Security-Policy`
- `X-Frame-Options`
- `X-Content-Type-Options`
- `Strict-Transport-Security`

#### 5. Credentials en dur dans `config.php:5-8`
Les identifiants de base de donnees sont directement dans le code source. Doivent etre dans un fichier `.env` hors du depot.

#### 6. Cles secretes exposees dans la base de donnees
`gitrepo.sql:43-48` : les `secret_key` et `unlock_code` sont en clair. Les cles API sont visibles dans le contenu des fichiers (`repository_files`, lignes 222-226).

---

### MAJEUR — Bugs fonctionnels

#### 7. Incoherence de noms de tables : `arg_apps` vs `functional_apps`
- `check-db.php:8` et `search.php:18` referencent `arg_apps`
- `repo.php:66` et `repo.php:74` referencent `functional_apps`
- **Une seule table existe** dans le schema SQL (`arg_apps`) — les requetes dans `repo.php` echouent silencieusement

#### 8. Lien profil casse dans `repo.php:248`
```php
<a href="profile.php">  <!-- Manque ?user=... -->
```
Le parametre `user` est absent, le lien ne fonctionne pas.

#### 9. `header.php` non utilise
`header.php` definit une structure complete (91 lignes) mais **aucune page ne l'inclut**. Chaque page redefinit son propre header — c'est du code mort.

#### 10. Constante `SITE_NAME` non definie
`header.php:7,37` : `<?php echo SITE_NAME; ?>` — la constante n'est definie nulle part.

---

### MAJEUR — Qualite du code

#### 11. Aucune separation des responsabilites
Chaque fichier PHP melange :
- Logique metier (requetes SQL, controle d'acces)
- Presentation (HTML, CSS inline de 100-300 lignes par page)
- JavaScript

**Etat actuel** :
```
arg/
  config.php      ← connexion DB + helpers + session : fichier "God"
  login.php       ← logique + vue
  dashboard.php   ← logique + vue + 300 lignes de CSS
  ...
```

**Structure recommandee** :
```
arg/
  config/         ← configuration
  models/         ← requetes DB
  controllers/    ← logique
  views/          ← templates HTML
  public/         ← CSS, images
```

#### 12. Duplication massive du code
| Pattern duplique | Fichiers |
|------------------|----------|
| Verification d'acces ARG | `profile.php:22-33`, `repo.php:28-35`, `file.php:29-36`, `issue.php:31-38` |
| Compteur de messages non lus | `header.php:54-56`, `dashboard.php:291-293` |
| Header HTML + navigation | Chaque page le redefinit entierement |
| Blocs CSS inline | Chaque page a son propre `<style>` |

#### 13. Valeurs magiques dispersees
- `dashboard.php:349` : `"dev_alpha"` en dur
- `search.php:63` : `unlock_order == 1` en dur
- Aucune constante nommee pour les valeurs de configuration

---

### MOYEN — Securite et bonnes pratiques

#### 14. Politique de mot de passe faible
`register.php:20` : minimum 6 caracteres, aucune exigence de complexite (majuscule, chiffre, caractere special).

#### 15. Aucun rate limiting sur le login
Pas de protection contre le brute force. Un attaquant peut tenter des milliers de combinaisons.

#### 16. Pas de timeout absolu de session
`config.php:107-112` : seul le timeout d'inactivite est verifie. Une session peut rester active indefiniment si l'utilisateur est actif.

#### 17. Comparaison de cles secretes sans `hash_equals()`
`repo.php:74` : `$entered_key === $app['secret_key']` — vulnerable aux attaques par timing.

#### 18. Cookies de session non securises
Aucun flag `Secure`, `HttpOnly`, ou `SameSite` configure sur les cookies de session.

---

### MOYEN — Frontend

#### 19. CSS massivement duplique dans `bck.html`
32+ classes CSS quasi-identiques (`.mur-icon1` a `.mur-icon16`, `.sol-icon1` a `.sol-icon32`). Devrait utiliser CSS Grid ou une boucle.

#### 20. Responsivite insuffisante
- `github-style.css` : un seul breakpoint a 768px, pas de tablette
- `bck.html` : dimensions fixes en pixels (256x79px), casse sur mobile
- `intheforest.html` : canvas 400x300px fixe, aucun responsive

#### 21. Accessibilite absente dans les mini-jeux
- `pag1.html` : interactions souris uniquement, pas de navigation clavier
- `bck.html` : images sans attribut `alt`, pas de HTML semantique
- `intheforest.html` : aucun attribut `lang`, pas de meta viewport
- Aucun attribut ARIA dans les fichiers HTML

#### 22. JavaScript inline non modularise
- `pag1.html` : 700+ lignes de JS inline
- Pas de gestion d'erreurs dans les fonctions async
- Parsing de texte fragile pour les declencheurs d'histoire

---

### MINEUR

| # | Probleme | Fichier |
|---|----------|---------|
| 23 | Nommage de variables incoherent (`$user`, `$current_user`, `$profile_user`, `$user_result`) | Tous |
| 24 | Fonction `has_access_to_repo()` definie dans `config.php:76-104` mais jamais utilisee | `config.php` |
| 25 | `logout.php` ne nettoie pas les cookies | `logout.php` |
| 26 | Collation `utf8mb4_general_ci` — `utf8mb4_unicode_ci` serait plus robuste | `gitrepo.sql` |
| 27 | Table `comments` vide, table `commits` vide, table `stars` vide | `gitrepo.sql` |
| 28 | Index manquants sur `messages.is_read`, `commits.committed_at` | `gitrepo.sql` |

---

## Resume

| Categorie | Critique | Majeur | Moyen | Mineur |
|-----------|----------|--------|-------|--------|
| Securite | 6 | — | 5 | — |
| Bugs fonctionnels | — | 4 | — | — |
| Qualite du code | — | 3 | — | 4 |
| Frontend | — | — | 4 | 2 |
| **Total** | **6** | **7** | **9** | **6** |

---

## Top 10 des actions prioritaires

1. **Ajouter des tokens CSRF** sur tous les formulaires POST
2. **Corriger `arg_apps` vs `functional_apps`** — unifier le nom de table
3. **Deplacer les credentials** dans un fichier `.env`
4. **Ajouter les headers de securite** (CSP, X-Frame-Options, etc.)
5. **Extraire les verifications d'acces** dans des fonctions reutilisables
6. **Inclure `header.php`** au lieu de dupliquer le header dans chaque page
7. **Ajouter du rate limiting** sur le login
8. **Renforcer la politique de mot de passe** (8+ caracteres, complexite)
9. **Securiser les cookies de session** (Secure, HttpOnly, SameSite)
10. **Rendre les mini-jeux responsifs** et accessibles au clavier

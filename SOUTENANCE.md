# 🎓 Document de Soutenance — Projet EduVerse (Symfony 6.4 / PHP 8.2)
### Module : Gestion des Utilisateurs — Youssef

---

## 1. 🗂️ Structure Complète du Projet

```
azerslimani/
├── src/
│   ├── Controller/
│   │   ├── Security/
│   │   │   ├── SecurityController.php       ← Login / Logout / Emergency Admin
│   │   │   ├── RegistrationController.php   ← Inscription + Face ID
│   │   │   ├── FaceAuthController.php       ← Connexion par reconnaissance faciale
│   │   │   ├── GoogleController.php         ← Redirection OAuth Google
│   │   │   ├── TwoFactorController.php      ← Page 2FA SMS
│   │   │   └── ResetPasswordController.php  ← Mot de passe oublié
│   │   ├── Admin/
│   │   │   ├── AdminController.php          ← Gestion des utilisateurs (CRUD)
│   │   │   └── DashboardController.php      ← Tableau de bord admin
│   │   └── User/
│   │       └── ProfileController.php        ← Edit Profile + Dashboards
│   │
│   ├── Entity/
│   │   ├── User.php                         ← Entité principale (20+ attributs)
│   │   ├── Wallet.php                       ← Portefeuille virtuel
│   │   ├── Formation.php / Cours.php        ← Modules e-learning
│   │   └── (15 autres entités du projet)
│   │
│   ├── Form/
│   │   ├── RegistrationFormType.php         ← Formulaire d'inscription
│   │   ├── ProfileFormType.php              ← Formulaire de profil
│   │   ├── ChangePasswordFormType.php       ← Réinitialisation mot de passe
│   │   └── UserFormType.php                 ← Ajout manuel par admin
│   │
│   ├── Security/
│   │   ├── AppAuthenticator.php             ← Authentification email/mot de passe
│   │   ├── GoogleAuthenticator.php          ← OAuth2 Google (KnpU)
│   │   ├── UserChecker.php                  ← Vérification statut compte
│   │   ├── AuthenticationSuccessHandler.php ← Redirection post-login
│   │   └── TwoFactor/                       ← Providers 2FA
│   │       └── TwilioSmsProvider.php        ← Envoi code SMS Twilio
│   │
│   ├── Repository/
│   │   └── UserRepository.php               ← 6 méthodes QueryBuilder custom
│   │
│   └── Service/
│       ├── FaceRecognitionService.php       ← Distance euclidienne (IA)
│       ├── TwilioService.php                ← API SMS Twilio
│       └── PasswordStrengthService.php      ← Génération mot de passe fort
│
├── templates/
│   ├── base.html.twig                       ← Navigation + includs globaux
│   ├── security/
│   │   ├── login.html.twig                  ← Connexion + Saisie vocale + Face ID
│   │   ├── register.html.twig               ← Inscription + capture faciale + 🎤
│   │   └── face_login.html.twig             ← Connexion biométrique
│   ├── reset_password/
│   │   ├── request.html.twig                ← Formulaire email oubli mdp
│   │   └── reset.html.twig                  ← Nouveau mot de passe
│   ├── admin/
│   │   └── user/ management.html.twig       ← Liste utilisateurs + filtres
│   └── student/ + instructor/
│       └── student-edit-profile.html.twig   ← Profil avec saisie vocale
│
├── public/assets/js/
│   ├── voice-typing.js                      ← Saisie vocale Web Speech API
│   ├── password-strength.js                 ← Barre de force + générateur mdp
│   └── face-registration.js                 ← Capture descriptor facial
│
├── config/packages/
│   ├── security.yaml                        ← Firewalls + Roles + 2FA
│   ├── scheb_two_factor.yaml                ← Config bundle 2FA
│   └── knpu_oauth2_client.yaml              ← Config OAuth Google
│
├── DEMO.md                                  ← Guide de démonstration
└── SPRINT1_DOC.md                           ← Documentation Sprint 1
```

---

## 2. 🗃️ Entité Principale : User.php

**Table BDD : `user`**

| Attribut | Type SQL | Description |
|---|---|---|
| `id` | INT (PK) | Identifiant auto-incrémenté |
| `email` | VARCHAR(180) | Unique, identifiant de connexion |
| `roles` | JSON | `["ROLE_STUDENT"]` ou `["ROLE_INSTRUCTOR"]` |
| `password` | VARCHAR(nullable) | Bcrypt/Argon2 hashé |
| `googleId` | VARCHAR(255, nullable) | ID fourni par Google OAuth |
| `fullName` | VARCHAR(255) | Nom complet |
| `role` | VARCHAR(255) | Label lisible : `"Student"` / `"Instructor"` / `"Admin"` |
| `isApproved` | BOOLEAN | `false` en attente, `true` après validation admin |
| `isBlocked` | BOOLEAN | Compte bloqué par admin |
| `isRejected` | BOOLEAN | Compte refusé par admin |
| `isVerified` | BOOLEAN | Email vérifié |
| `createdAt` | DATETIME_IMMUTABLE | Date de création du compte |
| `picture` | VARCHAR(255, nullable) | URL photo de profil |
| `jobTitle` | VARCHAR(255, nullable) | Intitulé de poste |
| `bio` | TEXT(nullable) | Biographie |
| `isTwoFactorEnabled` | BOOLEAN | Activation 2FA SMS |
| `phoneNumber` | VARCHAR(20, nullable) | Numéro pour Twilio |
| `twoFactorCode` | VARCHAR(6, nullable) | Code OTP temporaire |
| `twoFactorExpiresAt` | DATETIME_IMMUTABLE(nullable) | Expiration du code 2FA |
| `faceDescriptor` | JSON(nullable) | Vecteur 128 float (empreinte faciale) |

### Relations

| Type | Cible | Description |
|---|---|---|
| `OneToOne` | `Wallet` | Chaque user a un wallet unique |
| `ManyToMany` | `Formation` | Les étudiants s'inscrivent à des formations |
| `OneToMany` | `Certificate` | Un user peut avoir plusieurs certificats |
| `OneToMany` | `Resultat` | Un user a plusieurs résultats de quiz |

---

## 3. 🎛️ Controllers du Module User

### 3.1 SecurityController — `/login`, `/logout`

```php
#[Route('/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
// Affiche le formulaire de connexion avec la dernière erreur et le dernier username saisi.

#[Route('/logout', name: 'app_logout')]
public function logout(): void
// Intercepté par le firewall Symfony (méthode vide intentionnelle).
```

### 3.2 RegistrationController — `/register`

```php
#[Route('/register', name: 'app_register')]
public function register(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
```
**Étapes :**
1. Crée un objet `User` et génère le formulaire `RegistrationFormType`
2. Récupère `faceDescriptor` manuellement depuis `$request->request->get('faceDescriptor')` (JSON → array)
3. Hash le mot de passe avec `hashPassword()`
4. Assigne `ROLE_STUDENT` ou `ROLE_INSTRUCTOR` selon le choix du formulaire
5. `isApproved = false` → compte en attente d'approbation
6. Flash message + redirection vers `/login`

### 3.3 FaceAuthController — `/login/face` et `/login/face/check`

```php
#[Route('/login/face', name: 'app_face_login', methods: ['GET'])]
public function faceLogin(): Response
// Affiche la page de connexion biométrique

#[Route('/login/face/check', name: 'app_face_login_check', methods: ['POST'])]
public function faceLoginCheck(Request $request, UserRepository $repo, FaceRecognitionService $frs, Security $security): JsonResponse
```
**Algorithme :**
1. Reçoit JSON `{"descriptor": [128 valeurs float...]}`
2. Récupère tous les users avec `faceDescriptor IS NOT NULL`
3. Calcule la **distance euclidienne** entre le descripteur reçu et chaque descripteur stocké
4. Si `distance < 0.6` → visage reconnu (seuil standard face-api.js)
5. Vérifie `isRejected`, `isBlocked`, `!isApproved` → 403 si problème
6. Connexion programmatique : `$security->login($user, AppAuthenticator::class)`
7. Retourne JSON `{success, redirect, message}`

### 3.4 GoogleController — `/connect/google`

```php
#[Route('/connect/google', name: 'connect_google')]
public function connectAction(ClientRegistry $clientRegistry, Request $request): RedirectResponse
```
- Stocke le rôle choisi (student/instructor) en **session** ET en **cookie** (30min)
- Redirige vers Google OAuth via `KnpU OAuth2ClientBundle`

### 3.5 ProfileController — `/student/profile/edit`, `/instructor/dashboard`

```php
#[Route('/student/profile/edit', name: 'student_profile_edit')]
public function studentProfileEdit(Request $request, EntityManagerInterface $em): Response
// Charge ProfileFormType, traite la modification du profil étudiant

#[Route('/instructor/profile/edit', name: 'instructor_profile_edit')]
public function instructorProfileEdit(...): Response
// Identique pour les instructeurs
```

### 3.6 AdminController — Gestion des users

```php
#[Route('/admin/students', name: 'admin_students')]
public function students(...)  // Liste paginée des étudiants

#[Route('/admin/user/{id}/approve', name: 'admin_user_approve')]
public function approve(User $user, EntityManagerInterface $em)
// setIsApproved(true) + flush

#[Route('/admin/user/{id}/reject', name: 'admin_user_reject')]
public function reject(User $user, EntityManagerInterface $em)
// setIsRejected(true) + setIsApproved(false) + flush

#[Route('/admin/user/{id}/toggle-block', name: 'admin_user_toggle_block')]
public function toggleBlock(User $user, EntityManagerInterface $em)
// isBlocked(!isBlocked()) + flush
```

---

## 4. 📋 Formulaires (Form/)

### 4.1 RegistrationFormType

| Champ | Type | Contraintes |
|---|---|---|
| `fullName` | TextType | NotBlank |
| `email` | TextType | NotBlank, Email |
| `role` | ChoiceType | NotBlank (Student/Instructor, non mappé) |
| `agreeTerms` | CheckboxType | IsTrue (non mappé) |
| `plainPassword` | PasswordType | NotBlank, Length(min:6), **PasswordStrength(minScore:4)** |

### 4.2 ProfileFormType

| Champ | Type | Contraintes |
|---|---|---|
| `fullName` | TextType | NotBlank |
| `email` | TextType | **disabled: true** (lecture seule) |
| `jobTitle` | TextType | required: false |
| `bio` | TextareaType | required: false |
| `picture` | TextType | required: false (URL image) |
| `isTwoFactorEnabled` | CheckboxType | required: false |
| `phoneNumber` | TelType | required: false |

### 4.3 ChangePasswordFormType (Reset Password)

| Champ | Type | Contraintes |
|---|---|---|
| `plainPassword` | RepeatedType (Password) | NotBlank, Length(min:12), **PasswordStrength(minScore:4)**, **NotCompromisedPassword** |

> ⚠️ **`NotCompromisedPassword`** : vérifie que le mot de passe n'est pas dans une base de données de mots de passe compromis (Have I Been Pwned API).

---

## 5. 🔍 Repository Custom — UserRepository.php

| Méthode | Description |
|---|---|
| `countByRole(string $role)` | Compte les users d'un rôle donné via `LIKE "%ROLE_STUDENT%"` |
| `countPending(string $role)` | Compte les comptes en attente (isApproved=false, isRejected=false) |
| `countApproved(string $role)` | Compte les comptes approuvés |
| `countRejected(string $role)` | Compte les comptes rejetés |
| `countBlocked()` | Compte les comptes bloqués |
| `findUsersByRoleAndStatus(role, status, search, sort, direction)` | **QueryBuilder complet** avec filtrage par statut (pending/approved/rejected/blocked), recherche fulltext sur fullName + email, tri dynamique |

```php
// Exemple de la méthode la plus complexe :
public function findUsersByRoleAndStatus(string $role, ?string $status = null, ?string $search = null, string $sort = 'id', string $direction = 'DESC')
{
    $qb = $this->createQueryBuilder('u')
        ->where('u.roles LIKE :role')
        ->setParameter('role', '%"' . $role . '"%');

    // Filtre par statut (pending/approved/rejected/blocked)
    // Recherche fulltext : fullName LIKE :search OR email LIKE :search
    // Tri dynamique : orderBy('u.' . $sort, $direction)
    return $qb; // Retourne le QueryBuilder pour pagination
}
```

---

## 6. 📦 Bundles Externes (non Symfony natif)

| Bundle / Package | Usage dans le code |
|---|---|
| `knpuniversity/oauth2-client-bundle` | `GoogleAuthenticator.php` — OAuth2 avec Google |
| `league/oauth2-google` | Provider Google pour KnpU |
| `twilio/sdk` | `TwilioService.php` — Envoi de SMS 2FA |
| `scheb/2fa-bundle` | `TwoFactorController.php` + `security.yaml` — Système 2FA |
| `symfonycasts/reset-password-bundle` | `ResetPasswordController.php` — Flux reset password |
| `knplabs/knp-paginator-bundle` | `AdminController.php` + `Paginator` service — Pagination des listes |
| `dompdf/dompdf` (via PdfService) | `PdfService.php` — Génération de PDF (certificats, exports) |

---

## 7. 🤖 Intelligence Artificielle : Reconnaissance Faciale

### Librairie : `face-api.js` (TensorFlow.js)

**Comment ça fonctionne :**

```
ÉTAPE 1 - Inscription (Frontend)
  face-registration.js → face-api.js → detectSingleFace() + computeFaceDescriptor()
  → Vecteur Float32Array de 128 valeurs (empreinte faciale unique)
  → Envoyé au serveur via <input type="hidden" name="faceDescriptor">
  → Stocké en base de données comme JSON

ÉTAPE 2 - Connexion (Frontend → Backend)
  face_login.html.twig → face-api.js → capture webcam en temps réel
  → fetch POST /login/face/check { descriptor: [128 floats] }

ÉTAPE 3 - Comparaison (Backend PHP)
  FaceAuthController → FaceRecognitionService::euclideanDistance()
  
  distance = sqrt( Σ (a[i] - b[i])² )   pour i = 0 à 127
  
  Si distance < 0.6 → MÊME PERSONNE ✅
  Si distance ≥ 0.6 → VISAGE INCONNU ❌
```

### Code Clé — FaceRecognitionService.php

```php
public function euclideanDistance(array $d1, array $d2): float
{
    $sum = 0.0;
    foreach ($d1 as $index => $value1) {
        $diff = $value1 - $d2[$index];
        $sum += $diff * $diff;
    }
    return sqrt($sum);
}

public function areFacesMatching(array $stored, array $live, float $threshold = 0.6): bool
{
    return $this->euclideanDistance($stored, $live) < $threshold;
}
```

---

## 8. 🔐 Configuration Sécurité — security.yaml

```yaml
role_hierarchy:
  ROLE_ADMIN: [ROLE_INSTRUCTOR, ROLE_STUDENT]  # L'admin hérite de tous les rôles
  ROLE_INSTRUCTOR: [ROLE_STUDENT]               # L'instructeur hérite du rôle étudiant

firewalls:
  main:
    # 2 authenticateurs actifs simultanément
    custom_authenticator:
      - App\Security\AppAuthenticator    ← Email + Mot de passe
      - App\Security\GoogleAuthenticator ← Google OAuth2

    user_checker: App\Security\UserChecker  ← Vérifie isBlocked / isRejected / isApproved
    
    two_factor:
      auth_form_path: 2fa_login         ← Route du formulaire 2FA
      check_path: 2fa_login_check       ← Route de vérification du code

    remember_me:
      lifetime: 604800   ← "Se souvenir de moi" pendant 7 jours

access_control:
  - { path: ^/2fa, role: IS_AUTHENTICATED_2FA_IN_PROGRESS }
```

### UserChecker.php — Garde-fou de connexion

```php
public function checkPreAuth(UserInterface $user): void
{
    if ($user->isRejected())   → throw "Compte rejeté"
    if ($user->isBlocked())    → throw "Compte bloqué"
    if (!$user->isApproved())  → throw "Compte en attente d'approbation"
}
```

---

## 9. 🌐 APIs Externes

### API 1 — Twilio SMS (2FA)

```php
// TwilioService.php
$this->twilio = new Client($accountSid, $authToken);
$this->twilio->messages->create($to, [
    'from' => $this->fromNumber,
    'body' => "Votre code de sécurité : $code"
]);
```
**Variables `.env` requises :**
```
TWILIO_SID=ACxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxx
TWILIO_FROM=+1xxxxxxxxxx
```

### API 2 — Google OAuth2

```php
// GoogleAuthenticator.php
$googleUser = $client->fetchUserFromToken($accessToken);
$email = $googleUser->getEmail();
$googleId = $googleUser->getId();
$name = $googleUser->getName();
```
**Variables `.env` requises :**
```
GOOGLE_CLIENT_ID=xxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxx
```

---

## 10. ✨ Fonctionnalités JavaScript Avancées

### 10.1 Saisie Vocale — `voice-typing.js`
- **API** : `window.webkitSpeechRecognition` (Web Speech API)
- **Mode** : `continuous = true`, `interimResults = true` (mots s'affichent en temps réel)
- **Durée** : 12 secondes max, bouton 🎤 avec animation pulse rouge pendant l'écoute
- **Substitutions** : `at` → `@`, `point` → `.`, `tiret` → `-`, `underscore` → `_`
- **Pages** : Login, Register, Forgot Password, Edit Profile (étudiant + instructeur)

### 10.2 Force du Mot de Passe — `password-strength.js`
- Barre de progression colorée (rouge → jaune → vert) en temps réel
- Bouton "Générer" : crée un mot de passe de **24 caractères** avec `window.crypto.getRandomValues()`
- Algorithme Fisher-Yates shuffle pour une randomisation cryptographique

### 10.3 Capture Faciale — `face-registration.js`
- Charge les modèles TensorFlow : `ssd_mobilenetv1`, `face_landmark_68_net`, `face_recognition_net`
- Détecte le visage en temps réel via `getUserMedia()`
- Calcule le descripteur 128D et le stocke dans `<input type="hidden" name="faceDescriptor">`

---

## 11. 📜 Commits GitHub

```
e2c68dc (HEAD → youssef-dernier-travail) ajout du travail de youssef
```

> ℹ️ Le projet est sur la branche `youssef-dernier-travail` avec un commit principal regroupant l'ensemble du développement du module utilisateur.

---

## 12. 📄 Templates Twig Principaux

| Template | Fonctionnalités affichées |
|---|---|
| `security/login.html.twig` | Formulaire login, bouton Face ID, bouton Google, 🎤 saisie vocale, flash messages |
| `security/register.html.twig` | Formulaire inscription, sélection rôle, capture faciale (vidéo), barre de force du mot de passe, 🎤, Google |
| `security/face_login.html.twig` | Flux connexion biométrique (webcam temps réel + face-api.js) |
| `security/2fa_form.html.twig` | Formulaire saisie code OTP (6 chiffres) reçu par SMS |
| `reset_password/request.html.twig` | Email de demande de réinitialisation + 🎤 |
| `reset_password/reset.html.twig` | Nouveau mot de passe + barre de force |
| `admin/user/management.html.twig` | Tableau users avec filtres, pagination, boutons Approve/Reject/Block |
| `student/student-edit-profile.html.twig` | Formulaire profil + 2FA toggle + 🎤 |
| `instructor/instructor-edit-profile.html.twig` | Identique au profil étudiant |

---

*Document généré pour la soutenance — EduVerse Platform — Symfony 6.4 / PHP 8.2*

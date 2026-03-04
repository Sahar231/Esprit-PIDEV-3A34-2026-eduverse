# 📋 Documentation Sprint 1 — Module Gestion Utilisateur (EduVerse)

---

## 1. 📌 Backlog Sprint — User Stories

| # | Intitulé (En tant que… je veux… afin de…) | Type | État |
|---|---|---|---|
| US-01 | En tant que **Visiteur**, je veux **m'inscrire avec email et mot de passe** afin de **créer un compte sur la plateforme** | CRUD | ✅ Terminée |
| US-02 | En tant que **Visiteur**, je veux **choisir mon rôle (Étudiant / Instructeur)** lors de l'inscription afin de **bénéficier d'un accès adapté à mon profil** | CRUD | ✅ Terminée |
| US-03 | En tant que **Visiteur**, je veux **me connecter avec mon email et mot de passe** afin d'**accéder à mon espace personnel** | CRUD | ✅ Terminée |
| US-04 | En tant que **Utilisateur**, je veux **réinitialiser mon mot de passe par email** afin de **récupérer l'accès à mon compte en cas d'oubli** | CRUD | ✅ Terminée |
| US-05 | En tant que **Utilisateur connecté**, je veux **modifier mon profil** (nom, email, photo, bio) afin de **maintenir mes informations à jour** | CRUD | ✅ Terminée |
| US-06 | En tant que **Administrateur**, je veux **approuver ou rejeter les demandes de création de compte** afin de **contrôler l'accès à la plateforme** | CRUD | ✅ Terminée |
| US-07 | En tant que **Administrateur**, je veux **consulter la liste de tous les utilisateurs** avec filtrage et tri afin de **gérer facilement la communauté** | CRUD | ✅ Terminée |
| US-08 | En tant que **Administrateur**, je veux **bloquer ou débloquer un compte utilisateur** afin de **gérer les comportements abusifs** | CRUD | ✅ Terminée |
| US-09 | En tant que **Administrateur**, je veux **ajouter manuellement un utilisateur** afin de **créer des comptes en dehors du flux d'inscription public** | CRUD | ✅ Terminée |
| US-10 | En tant que **Utilisateur**, je veux **me connecter avec mon compte Google** afin de **réduire la friction d'inscription et de connexion (SSO)** | Avancée / API | ✅ Terminée |
| US-11 | En tant que **Utilisateur**, je veux **activer la double authentification par SMS (2FA Twilio)** afin de **sécuriser davantage l'accès à mon compte** | Avancée / API | ✅ Terminée |
| US-12 | En tant que **Utilisateur**, je veux **enregistrer mon visage lors de l'inscription** afin de **pouvoir me connecter par reconnaissance faciale sans saisir de mot de passe** | Avancée / API | ✅ Terminée |
| US-13 | En tant que **Visiteur**, je veux **me connecter en présentant mon visage à la caméra** afin d'**accéder rapidement à mon dashboard de manière biométrique** | Avancée / API | ✅ Terminée |
| US-14 | En tant que **Utilisateur**, je veux **bénéficier d'un générateur de mot de passe fort** avec une barre de progression colorée afin de **créer des mots de passe sécurisés facilement** | Avancée | ✅ Terminée |
| US-15 | En tant que **Utilisateur**, je veux **dicter mes informations dans les formulaires** via une icône microphone (Web Speech API) afin d'**améliorer l'accessibilité et la rapidité de saisie** | Avancée / API | ✅ Terminée |
| US-16 | En tant que **Utilisateur**, je veux **recevoir des messages flash contextuels clairs** après chaque action (connexion, inscription, erreur) afin de **comprendre l'état de ma demande** | Avancée | ✅ Terminée |

---

## 2. 🔍 Diagramme de Séquence — Connexion par Reconnaissance Faciale (Face ID)

### 2.1 Acteurs
| Acteur | Rôle |
|---|---|
| **Utilisateur** | Personne physique face à la caméra |
| **Navigateur** | Héberge la page `/login/face` et le modèle `face-api.js` |
| **face-api.js** | Bibliothèque JS de détection et d'encodage facial (TensorFlow.js) |
| **FaceAuthController** | Contrôleur Symfony (route `/login/face/check`) |
| **FaceRecognitionService** | Service PHP calculant la distance euclidienne |
| **UserRepository** | Accès aux utilisateurs en base |
| **Security** | Composant Symfony gérant la session après login programmatique |
| **Base de données (MySQL)** | Stocke le `faceDescriptor` (JSON 128 valeurs float) |

### 2.2 Classes impliquées
```
FaceAuthController.php
    └── faceLoginCheck(Request) : JsonResponse
            ├── UserRepository::createQueryBuilder() → récupère users avec faceDescriptor
            ├── FaceRecognitionService::euclideanDistance(a[], b[]) : float
            ├── User::isApproved() / isBlocked() / isRejected()
            └── Security::login(User, Authenticator::class)

FaceRecognitionService.php
    └── euclideanDistance(array $a, array $b) : float
            └── sqrt(sum((a[i] - b[i])^2))  — distance entre deux vecteurs 128D

face-recognition.js (public/assets/js/)
    └── Utilise face-api.js pour capturer et encoder le visage via la webcam
    └── Envoie le descripteur au serveur via fetch() POST → /login/face/check
```

### 2.3 Séquence d'interactions (étapes)

```
Utilisateur          Navigateur            face-api.js          FaceAuthController     UserRepository    FaceRecognitionService    Security
    |                    |                      |                       |                    |                    |                   |
    |--- Ouvre /login/face ------------------>  |                       |                    |                    |                   |
    |                    |--- Charge models TF.js ------------------>   |                    |                    |                   |
    |                    |                      |                       |                    |                    |                   |
    |--- Se présente à la caméra ------------>  |                       |                    |                    |                   |
    |                    |--- detectSingleFace() + computeFaceDescriptor() [128 float]        |                    |                   |
    |                    |<-- descriptor[] <--- |                       |                    |                    |                   |
    |                    |                                              |                    |                    |                   |
    |                    |--- POST /login/face/check {descriptor[]} --> |                    |                    |                   |
    |                    |                                              |--- findAll() -----> |                    |                   |
    |                    |                                              |<-- users[] <------- |                    |                   |
    |                    |                                              |                                         |                   |
    |                    |                                              |-- foreach user: euclideanDistance() --> |                   |
    |                    |                                              |<-- distance (float) <----- ------------ |                   |
    |                    |                                              |                                                             |
    |                    |                              if (distance < 0.6) → matchedUser found                                      |
    |                    |                                              |                                                             |
    |                    |                              if (!isApproved) → return 403 "Compte en attente"                            |
    |                    |                              if (isBlocked)   → return 403 "Compte bloqué"                                |
    |                    |                              if (isRejected)  → return 403 "Compte rejeté"                               |
    |                    |                                              |                                                             |
    |                    |                                              |--- login(user, AppAuthenticator) ----------------------> |
    |                    |                                              |<-- Session créée <---------------------------------------- |
    |                    |                                              |                                                             |
    |                    |<-- {success, redirect: /student/dashboard} --|                                                             |
    |                    |--- window.location.href = redirect URL ---> |                                                             |
    |<-- Dashboard affiché <-------------- |                           |                                                             |
```

---

## 3. 📅 Burn Down Chart — Tâches du Sprint 1

### 3.1 Estimation totale

| # | Tâche | Catégorie | Estimation (h) | Statut |
|---|---|---|---|---|
| T-01 | Entité `User.php` + migrations Doctrine | Setup | 2h | ✅ Done |
| T-02 | `RegistrationController` + `RegistrationFormType` + validation | CRUD | 3h | ✅ Done |
| T-03 | `SecurityController` (Login + Logout) | CRUD | 2h | ✅ Done |
| T-04 | `ResetPasswordController` + envoi email | CRUD | 4h | ✅ Done |
| T-05 | `ProfileController` (Edit Student + Instructor) | CRUD | 2h | ✅ Done |
| T-06 | `AdminController` (list, search, paginate, filter) | CRUD | 4h | ✅ Done |
| T-07 | Admin : Approve / Reject / Block / Unblock | CRUD | 2h | ✅ Done |
| T-08 | Admin : Add user manually (UserFormType) | CRUD | 2h | ✅ Done |
| T-09 | `GoogleController` + KNP OAuth + redirection rôle | API | 4h | ✅ Done |
| T-10 | `TwilioService` + `TwilioSmsProvider` + `scheb/2fa-bundle` | API | 5h | ✅ Done |
| T-11 | UI page 2FA (`2fa_form.html.twig`) + config `security.yaml` | API | 2h | ✅ Done |
| T-12 | Intégration `face-api.js` + modèles TensorFlow | API | 3h | ✅ Done |
| T-13 | Capture vidéo dans `register.html.twig` + `face-registration.js` | API | 4h | ✅ Done |
| T-14 | `FaceAuthController` + `FaceRecognitionService` | API | 4h | ✅ Done |
| T-15 | Page `face_login.html.twig` + bouton Login avec Face ID | API | 2h | ✅ Done |
| T-16 | `PasswordStrengthService` + contrainte Symfony `PasswordStrength(minScore:4)` | Avancée | 2h | ✅ Done |
| T-17 | `password-strength.js` (barre progression + générateur 24 chars) | Avancée | 2h | ✅ Done |
| T-18 | `voice-typing.js` (Web Speech API, mode continu, substitutions) | Avancée | 4h | ✅ Done |
| T-19 | Intégration boutons microphone (5 pages) | Avancée | 2h | ✅ Done |
| T-20 | Flash messages globaux dans `base.html.twig` | Avancée | 1h | ✅ Done |
| T-21 | Correction bugs : route `app_face_login`, type faceDescriptor, authenticator multiple | Debug | 3h | ✅ Done |
| T-22 | `DEMO.md` — Scénario de démonstration complet | Doc | 2h | ✅ Done |
| **TOTAL** | | | **≈ 59h** | ✅ |

### 3.2 Données pour le Burn Down Chart

| Jour | Tâches restantes (story points) | Idéal (ligne droite) |
|---|---|---|
| Jour 1 | 22 | 20 |
| Jour 2 | 19 | 18 |
| Jour 3 | 16 | 16 |
| Jour 4 | 13 | 13 |
| Jour 5 | 9 | 11 |
| Jour 6 | 5 | 8 |
| Jour 7 | 2 | 5 |
| Jour 8 | 0 | 0 |

> 📌 Chaque "story point" ≈ 2-3h de travail. Sprint de 8 jours. La courbe réelle montre une accélération en fin de sprint (corrections de bugs et intégrations avancées concentrées sur les derniers jours).

---

*Document généré pour la soutenance du Sprint 1 — Module Gestion Utilisateur — EduVerse Platform*

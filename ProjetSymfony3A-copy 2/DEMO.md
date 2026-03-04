# 🎓 DEMO.md — Guide de Démonstration du Module Utilisateur (EduVerse)

Bienvenue dans le guide de démonstration du module utilisateur. Ce document couvre les **6 fonctionnalités avancées** implémentées dans le cadre de ce projet.

---

## 👤 Comptes de Démonstration

| Rôle | Email | Mot de passe | Statut |
|---|---|---|---|
| **Admin** | `admin@test.tn` | `Admin1234!` | Approuvé |
| **Instructeur** | `instructor@test.tn` | `Instructor1234!` | Approuvé |
| **Étudiant** | `student@test.tn` | `Student1234!` | Approuvé |
| **En attente** | `pending@test.tn` | *(compte à créer)* | EN_ATTENTE |

> 💡 Pour tester le workflow d'approbation, inscrivez un nouveau compte et connectez-vous en admin pour l'approuver ou le refuser.

---

## ✅ Fonctionnalité 1 — Double Authentification 2FA (SMS Twilio)

### Objectif
Sécuriser l'accès via un code SMS à usage unique valable **5 minutes**.

### Pré-requis
- Avoir un compte avec `isTwoFactorEnabled = true` et un numéro de téléphone valide configuré dans son profil.
- Le compte Twilio est en mode **essai** : seul le numéro `+21693008154` peut recevoir les SMS.

### Scénario de Test
1. Aller sur `/profile/edit` et activer la **case "Activer l'authentification 2FA"**.
2. Renseigner le numéro de téléphone au format international (ex : `+21693008154`).
3. Sauvegarder le profil.
4. Se déconnecter puis se reconnecter avec le bon email + mot de passe.
5. ✅ **Résultat attendu** : Vous êtes redirigé vers la page `/2fa` pour saisir le code à 6 chiffres reçu par SMS.
6. Entrer le bon code → Connexion réussie et redirection vers votre dashboard.
7. Entrer un mauvais code → Message d'erreur "Code invalide ou expiré".

---

## ✅ Fonctionnalité 2 — Connexion par Reconnaissance Faciale (Face ID)

### Objectif
Permettre à un utilisateur de s'inscrire et de se connecter via son visage (modèle `face-api.js`).

### Scénario A : Inscription avec Face ID
1. Aller sur `/register`.
2. Remplir les champs (Email, Nom, Mot de passe...).
3. Dans la section **"Identifiant Facial"**, cliquer sur **"Activer la caméra"** et autoriser l'accès.
4. Cliquer sur **"Scanner mon visage"** → Le badge passe de "Non configuré" à "✅ Visage enregistré".
5. Soumettre le formulaire.
6. ✅ **Résultat attendu** : Votre descripteur facial est enregistré dans la base de données.

### Scénario B : Connexion avec Face ID
1. Aller sur `/login`.
2. Cliquer sur **"Login with Face ID"**.
3. Sur la page de connexion biométrique `/login/face`, se placer face à la caméra.
4. ✅ **Résultat attendu** : Si le visage correspond, connexion automatique et redirection selon le rôle (Student/Instructor/Admin dashboard).
5. **Compte non approuvé ?** → Message : *"Votre compte est en attente d'approbation par l'administrateur."*

---

## ✅ Fonctionnalité 3 — Générateur de Mot de Passe Sécurisé

### Objectif
Aider l'utilisateur à créer un mot de passe fort et valider la robustesse côté serveur.

### Scénario de Test
1. Aller sur `/register` (ou sur la page de reset de mot de passe `/reset-password`).
2. Observer la **barre de progression colorée** sous le champ "Password" :
   - 🔴 Rouge = Faible
   - 🟡 Jaune = Moyen
   - 🟢 Vert = Très fort
3. Taper un mot de passe faible (ex: `azerty`) → La barre reste rouge.
4. Cliquer sur le bouton **"✨ Générer un mot de passe sécurisé"** → Un mot de passe de **24 caractères** (majuscules, chiffres, symboles) est injecté dans le champ. La barre passe au vert.
5. Soumettre le formulaire.
6. ✅ **Résultat côté serveur** : Si le mot de passe est trop faible, Symfony affiche : *"Le mot de passe doit contenir au moins 12 caractères avec des majuscules, des chiffres et des caractères spéciaux."*

---

## ✅ Fonctionnalité 4 — Saisie Vocale (Web Speech API)

### Objectif
Permettre à l'utilisateur de dicter ses informations dans les formulaires.

### Compatibilité
- ✅ Google Chrome (recommandé)
- ✅ Microsoft Edge
- ❌ Firefox (non supporté sans configuration spéciale)

### Scénario de Test (sur Login, Register, Forgot Password, Edit Profile)
1. Cliquer sur l'icône **microphone rouge** 🎙️ à droite d'un champ Email ou Nom.
2. Si c'est la première fois, autoriser l'accès au microphone dans le navigateur.
3. Observer le bouton : il s'anime (halo rouge clignotant) pour indiquer l'écoute active.
4. Dicter votre texte. Les mots s'affichent **en temps réel** dans le champ mentre que vous parlez.
5. Attendre la fin de la reconnaissance (bouton revient à son état normal après max 12 secondes).
6. **Pour un champ Email :** Dites votre email naturellement. Ejemplo: *"john point doe at gmail point com"* → le "at" sera converti en `@`.

### Règle de conversion "@"
| Ce que vous dites | Ce qui s'écrit dans le champ |
|---|---|
| `at` (mot seul en anglais) | `@` |
| `arobase` | `arobase` *(littéral, pas converti)* |

---

## ✅ Fonctionnalité 5 — Flash Messages (Transversaux)

### Objectif
Afficher des messages contextuels clairs après chaque action importante.

### Messages testables

| Action | Type | Message attendu |
|---|---|---|
| Inscription réussie | ℹ️ Info | "Votre compte est en attente d'approbation..." |
| Connexion d'un compte bloqué | ⛔ Danger | "Votre compte est bloqué..." |
| Connexion d'un compte rejeté | ⛔ Danger | "Votre compte a été refusé..." |
| Connexion Google avec email déjà existant | ⚠️ Warning | "Cette adresse email est déjà utilisée..." |
| Envoi email forgot password | ✅ Success | "Un email a été envoyé..." |
| Profil sauvegardé | ✅ Success | "Votre profil a été mis à jour." |

---

## ✅ Fonctionnalité 6 — Workflow Admin d'Approbation

### Scénario de Test
1. S'inscrire avec un nouveau compte.
2. Se connecter en tant qu'Admin (`admin@test.tn`).
3. Accéder à **Dashboard Admin → Pending Approvals**.
4. Cliquer sur **"Approve"** pour approuver le compte de test.
5. ✅ L'utilisateur peut désormais se connecter normalement (standard, Google ou Face ID).
6. Cliquer sur **"Reject"** pour refuser un autre compte.
7. Quand l'utilisateur refusé essaie de se connecter → Message d'erreur clair.

---

## 🛠️ Commandes utiles

```bash
# Démarrer le serveur de développement
symfony serve

# Vider le cache
php bin/console cache:clear

# Mettre à jour le schéma de base de données (si nécessaire)
php bin/console doctrine:schema:update --force

# Voir les routes disponibles
php bin/console debug:router | grep -i face
php bin/console debug:router | grep -i login
```

---

## 📂 Arborescence des fichiers clés

```
src/
├── Controller/Security/
│   ├── FaceAuthController.php       ← Connexion Face ID
│   ├── RegistrationController.php   ← Inscription
│   └── SecurityController.php       ← Login standard
├── Service/
│   ├── FaceRecognitionService.php   ← Distance euclidienne
│   ├── TwilioService.php            ← Envoi SMS 2FA
│   └── PasswordStrengthService.php  ← Génération mot de passe
public/assets/js/
├── voice-typing.js                  ← Saisie vocale Web Speech API
├── password-strength.js             ← Barre de force & générateur
└── face-registration.js             ← Capture descripteur facial
templates/security/
├── login.html.twig                  ← Page de connexion
├── register.html.twig               ← Page d'inscription (avec Face ID)
└── face_login.html.twig             ← Page connexion biométrique
```

---

*Document rédigé pour la présentation du Module Utilisateur — EduVerse Platform*

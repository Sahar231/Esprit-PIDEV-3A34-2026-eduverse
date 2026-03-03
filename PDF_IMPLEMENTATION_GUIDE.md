# 📊 Intégration dompdf - Téléchargement PDF des Résultats de Quiz

## ✅ Étapes Complétées

### 1. Installation de dompdf
```bash
composer require dompdf/dompdf
```
✅ **Statut:** Installé avec succès (v3.1.4)

---

## 📋 Fichiers Créés/Modifiés

### 1. **src/Controller/Student/QuizController.php**

#### Imports ajoutés:
```php
use Dompdf\Dompdf;
use Dompdf\Options;
```

#### Nouvelle méthode: `downloadResultPdf()`
**Route:** `GET /student/quiz/submission/{submissionId}/pdf`  
**Alias:** `student_attempt_result_pdf`  
**Protections:**
- ✅ Authentification ROLE_STUDENT requise
- ✅ Vérification que la soumission appartient à l'étudiant connecté

**Fonctionnalités:**
- Récupère QuizSubmission par ID
- Charge Quiz + Questions + Réponses
- Vérifie la propriété (Student)
- Rend template Twig PDF
- Configure Dompdf avec:
  - Encodage UTF-8
  - Police DejaVu Sans
  - Support HTML5
  - Orientation A4
- Génère PDF et retourne avec headers corrects
  - `Content-Type: application/pdf`
  - `Content-Disposition: attachment; filename="resultat-quiz-{id}.pdf"`

#### Modification de `submit()`:
- Stockage de l'objet `$submission` créé
- Passage du `$submission` au template Twig pour accès à l'ID

---

### 2. **templates/pdf/student_result.html.twig** (NOUVEAU)

Structure du PDF:
```
┌─────────────────────────────────────┐
│     📊 Résultat du Quiz             │
│     Titre du Quiz                   │
├─────────────────────────────────────┤
│ Étudiant: Nom                       │
│ Email: email@exemple.com            │
│ Date: 25/02/2026 à 14:30           │
├─────────────────────────────────────┤
│ ┌──────┬──────────┬───────┐        │
│ │Score │Pourcentage│Résultat│       │
│ │3/5   │60%       │✓Réussi │       │
│ └──────┴──────────┴───────┘        │
├─────────────────────────────────────┤
│ 📝 DÉTAILS DES RÉPONSES             │
│ ┌──────────────────────────────────┐│
│ │Question 1                        ││
│ │Votre réponse: JavaScript        ││
│ │Bonne réponse: JavaScript        ││
│ │Status: ✓ Correct                ││
│ └──────────────────────────────────┘│
├─────────────────────────────────────┤
│ Plateforme de Gestion d'Évaluation  │
│ Généré le: 25/02/2026 14:30:00     │
└─────────────────────────────────────┘
```

**Styles appliqués:**
- Police: DejaVu Sans (compatible PDF)
- Gradient header: #667eea → #764ba2
- Couleurs des statuts:
  - ✓ Correct: Vert (#28a745)
  - ✗ Incorrect: Rouge (#dc3545)
- Mise en page A4 portrait
- UTF-8 encodage
- Saut de page automatique pour questions longues

---

### 3. **templates/student/quiz/result_new.html.twig** (MODIFIÉ)

#### Modification:
Ajout du bouton de téléchargement PDF dans la section "Action Buttons":

```twig
{% if submission %}
    <a href="{{ path('student_attempt_result_pdf', {submissionId: submission.id}) }}" 
       class="btn btn-success btn-lg" target="_blank">
        <i class="fas fa-file-pdf me-2"></i>Télécharger Résultat en PDF
    </a>
{% endif %}
```

**Placement:** Avant le bouton "Réessayer ce Quiz"

**Comportement:**
- Bouton vert avec icône PDF
- S'affiche seulement pour utilisateurs authentifiés
- Ouvre le PDF en nouvel onglet (téléchargement automatique)
- Libellé en français

---

## 🔒 Sécurité et Contrôles

✅ **Authentification:**
```php
if (!$this->getUser()) {
    throw $this->createAccessDeniedException('Connexion requise');
}
```

✅ **Ownership Check (CSRF Prevention):**
```php
if ($submission->getStudent()->getId() !== $this->getUser()->getId()) {
    throw $this->createAccessDeniedException('Accès refusé');
}
```

✅ **Validation:**
- Vérification existence QuizSubmission
- Vérification existence Quiz et Questions
- Gestion des réponses manquantes

---

## 📱 Flux d'Utilisation

### Scénario Utilisateur:
1. **Student complète un quiz**
   → Clique "Soumettre" sur dernière question

2. **Résultats affichés**
   → Page `result_new.html.twig` avec:
   - Score (3/5)
   - Pourcentage (60%)
   - Statut (Réussi/Échoué)
   - Détail de chaque question

3. **Télécharge PDF**
   → Clique bouton "Télécharger Résultat en PDF"
   → Route: `/student/submission/123/pdf`
   → Fichier: `resultat-quiz-123.pdf` téléchargé

4. **Ouvre le PDF**
   → Visualise le résultat mis en forme
   → Peut imprimer ou sauvegarder

---

## 🧪 Test de la Fonctionnalité

### Prérequis:
- ✅ Symfony server running: `symfony server:start`
- ✅ Database with test data
- ✅ Utilisateur authentifié en tant qu'étudiant

### Étapes de test:
1. Aller à `/student/quiz/`
2. Sélectionner un quiz
3. Répondre aux questions
4. Cliquer "Soumettre"
5. Sur page résultats → Cliquer "Télécharger Résultat en PDF"
6. Vérifier que PDF se télécharge: `resultat-quiz-{id}.pdf`
7. Ouvrir PDF avec lecteur PDF
8. Vérifier le formatage et les informations

---

## 📊 Informations dans le PDF

| Section | Données |
|---------|---------|
| **Header** | Titre du quiz |
| **Info Étudiant** | Nom, Email, Date soumission |
| **Résumé** | Score, Pourcentage, Résultat |
| **Questions** | Question, Réponse étudiante, Bonne réponse*, Statut |

*Bonne réponse affichée seulement si l'étudiant a mal répondu

---

## 🎨 Personnalisation Possible

### Modifier la mise en page du PDF:
Éditer `templates/pdf/student_result.html.twig`:
```twig
{# Changer couleurs #}
<style>
    .header h1 { color: #votre-couleur; }
    
    {# Changer fontes #}
    * { font-family: 'DejaVu Serif', serif; }
    
    {# Ajouter logo #}
    <img src="path/to/logo.png" />
</style>
```

### Ajouter signature instructeur:
```twig
<div class="footer">
    <p>Signé par: {{ quiz.instructor.firstname }}</p>
</div>
```

### Changer le nom du fichier:
Dans `downloadResultPdf()`:
```php
$filename = sprintf('Quiz-%s-%s.pdf', 
    $quiz->getTitle(),
    $submission->getId()
);
```

---

## 🐛 Dépannage

| Erreur | Solution |
|--------|----------|
| **404 - Route not found** | Cache clear: `php bin/console cache:clear` |
| **Access Denied** | Vérifier authentification utilisateur |
| **PDF vierge** | Vérifier que Twig template est bien formaté |
| **Caractères corrompus** | Vérifier encodage UTF-8 dans Dompdf Options |
| **Polices manquantes** | Dompdf utilise DejaVu standard (incluses) |
| **Classe QuizSubmission not found** | Vérifier namespace Entity |

---

## 📝 Notes de Développement

- **Entity:** Utilise `QuizSubmission` existante (pas `Attempt`)
- **Student:** Stocké dans `QuizSubmission::$student` (User)
- **Answers:** JSON array dans `QuizSubmission::$answers`
- **Dompdf:** v3.1.4 (stable, maintenu)
- **Font:** DejaVu Sans (gratuit, compatible PDF)
- **Twig:** Rendering HTML → PDF (pas de dependencies supplémentaires)

---

## ✨ Fonctionnalités Bonus Possibles

1. **Email du PDF**
   ```php
   // Dans downloadResultPdf()
   $mailer->send();
   ```

2. **Archive ZIP de plusieurs résultats**
   ```php
   $zip = new ZipArchive();
   foreach ($submissions as $submission) { ... }
   ```

3. **QR code vers résultat en ligne**
   ```php
   <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=...">
   ```

4. **Comparaison avec résultats précédents**
   ```twig
   {% if submission.quiz.submissions|length > 1 %}
       Progression: {% endif %}
   ```

---

## ✅ Checklist Finale

- ✅ dompdf installé
- ✅ Controller avec méthode `downloadResultPdf()`
- ✅ Route protégée `/student/submission/{id}/pdf`
- ✅ Vérification sécurité (authentification + ownership)
- ✅ Template Twig PDF formaté
- ✅ Encodage UTF-8
- ✅ Police DejaVu Sans
- ✅ Bouton dans interface résultat
- ✅ Passage de `$submission` au template résultats
- ✅ Cache cleared
- ✅ Prêt pour produciton

---

**Implémentation complétée le: 25 février 2026**

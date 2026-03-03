# Guide des Quiz de Test

## Installation des Fixtures

Exécutez la commande suivante pour charger les données de test :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

## Quiz Disponibles après Installation:

### 1️⃣ **PHP Basics** 
- **Niveau:** Facile  
- **Durée:** 15 minutes
- **Questions:** 5
- **Sujet:** Fondamentaux PHP
- **Réponses attendues:**
  - Q1: `echo` (afficher du texte)
  - Q2: `$` (symbole de variable)
  - Q3: `//` (commentaire)
  - Q4: `strlen` (longueur de chaîne)
  - Q5: `array` (type tableau)

---

### 2️⃣ **JavaScript ES6**
- **Niveau:** Intermédiaire
- **Durée:** 20 minutes  
- **Questions:** 5
- **Sujet:** JavaScript moderne
- **Réponses attendues:**
  - Q1: `const` (variable constante)
  - Q2: `=>` (fonction fléchée)
  - Q3: `object` (type données)
  - Q4: `getElementById` (sélecteur DOM)
  - Q5: `Babel` (transpileur)

---

### 3️⃣ **SQL Database**
- **Niveau:** Difficile
- **Durée:** 30 minutes
- **Questions:** 5
- **Sujet:** SQL avancé
- **Réponses attendues:**
  - Q1: `*` (toutes colonnes)
  - Q2: `INNER JOIN` (jointure)
  - Q3: `INSERT` (insérer données)
  - Q4: `CREATE TABLE` (créer table)
  - Q5: `COUNT` (compter lignes)

---

### 4️⃣ **Symfony Framework**
- **Niveau:** Intermédiaire
- **Durée:** 25 minutes
- **Questions:** 5
- **Sujet:** Framework Symfony
- **Réponses attendues:**
  - Q1: `EntityManager` (gestionnaire entités)
  - Q2: `templates` (dossier templates)
  - Q3: `services.yaml` (config)
  - Q4: `make:entity` (commande)
  - Q5: `Twig` (moteur templates)

---

## Processus de Test Complet

### Étape 1: Accéder à l'Interface Student
```
http://localhost:8000/student/quiz
```

### Étape 2: Sélectionner un Quiz
- Cliquez sur "Commencer" sur l'une des cartes

### Étape 3: Passer le Quiz
- Lisez chaque question
- Entrez votre réponse (case-insensitive, espaces ignorés)
- Cliquez sur "Soumettre le Quiz"

### Étape 4: Affichage des Résultats
- Score affiché en grand
- Détail de chaque réponse (correcte/incorrecte)
- Comparaison réponse étudiante vs bonne réponse
- Statistiques de performance

### Étape 5: Réessayer ou Revenir
- Bouton "Réessayer ce Quiz"
- Bouton "Retour aux Quiz"

---

## Pour l'Interface Admin

### Accès Admin Quiz
```
http://localhost:8000/admin/quiz
```

### Opérations Possibles:
1. **Liste des Quiz** - Voir tous les quiz créés
2. **Créer Quiz** - Nouveau quiz avec titre, niveau, durée
3. **Éditer Quiz** - Modifier les infos et ajouter/supprimer questions
4. **Supprimer Quiz** - Avec confirmation
5. **Gérer Questions** - Ajouter/éditer/supprimer questions

---

## Notes Importantes

✅ Les réponses sont **case-insensitive** (PHP = php = PHP)  
✅ Les espaces avant/après sont **ignorés**  
✅ Le système calcule le score en temps réel  
✅ Les résultats sont **sauvegardés en base de données**

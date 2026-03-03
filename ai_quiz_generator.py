import os
import re
import json
import subprocess
import sys

# ============================================================
# Quiz Generator (SANS API EXTERNE - Utilise Ollama local)
# ============================================================
# Installation: 
#   1. Téléchargez Ollama depuis https://ollama.ai
#   2. Lancez: ollama pull mistral (ou ollama pull llama2)
#   3. Lancez: ollama serve
#   4. Puis: python ai_quiz_generator.py --topic "Routing Symfony" ...
# ============================================================


def build_prompt(topic, level, count):
    """Crée un prompt pour générer le quiz"""
    lvl_map = {
        "facile": "très facile, concepts de base uniquement",
        "intermediaire": "intermédiaire, nécessite une compréhension",
        "difficile": "avancé, nécessite une connaissance profonde",
    }
    lvl = lvl_map.get(level, "intermédiaire")
    
    return f"""Tu es un expert en création de quiz pédagogiques. 
Génère EXACTEMENT {count} questions de quiz en français sur le sujet : "{topic}" 
au niveau {lvl}.

IMPORTANT: Retourne UNIQUEMENT du JSON valide, aucun texte avant ou après.

Format JSON EXACT:
{{
  "questions": [
    {{
      "text": "Texte de la question en français",
      "choices": ["Choix 1", "Choix 2", "Choix 3", "Choix 4"],
      "answerIndex": 0,
      "explanation": "Pourquoi cette réponse est correcte"
    }}
  ]
}}

Exigences STRICTES:
- EXACTEMENT {count} questions
- EXACTEMENT 4 choix par question
- answerIndex doit être 0, 1, 2 ou 3
- Tout le texte EN FRANÇAIS
- Les explications doivent être claires et pédagogiques
- Retourne UNIQUEMENT l'objet JSON, rien d'autre

Génère le quiz maintenant:
"""


def generate_quiz_with_ollama(topic, level="intermediaire", count=5):
    """Génère un quiz via Ollama (modèle local)"""
    prompt = build_prompt(topic, level, count)
    
    try:
        # Appel à Ollama via CLI
        result = subprocess.run(
            ["ollama", "run", "mistral", prompt],
            capture_output=True,
            text=True,
            timeout=120
        )
        
        if result.returncode != 0:
            raise RuntimeError(f"Ollama error: {result.stderr}")
        
        text = result.stdout.strip()
        
        # Extraire le JSON s'il y a du texte autour
        match = re.search(r"(\{[\s\S]*\})", text)
        if match:
            text = match.group(1)
        
        quiz = json.loads(text)
        validate_quiz(quiz, count)
        return quiz
        
    except FileNotFoundError:
        print("❌ Ollama n'est pas installé ou n'est pas dans le PATH")
        print("📥 Installez Ollama: https://ollama.ai")
        print("🚀 Après installation, lancez: ollama serve")
        print("📦 Puis téléchargez un modèle: ollama pull mistral")
        sys.exit(1)
    except subprocess.TimeoutExpired:
        raise RuntimeError("Timeout Ollama - modèle trop lent ou pas en cours d'exécution")


def generate_quiz_template(topic, level="intermediaire", count=5):
    """
    Génère un quiz basé sur des TEMPLATES intelligents (sans aucune API)
    Utile si Ollama n'est pas disponible
    """
    questions = []
    
    # Template d'exemples pour différents sujets
    templates = {
        "symfony": [
            {
                "text": "Qu'est-ce qu'une Route dans Symfony ?",
                "choices": ["Une liaison HTTP", "Un fichier de configuration", "Une définition d'URL et d'action", "Une base de données"],
                "answerIndex": 2,
                "explanation": "Une route dans Symfony mappe une URL à un contrôleur/action"
            },
            {
                "text": "Quel est le fichier de configuration principal des routes ?",
                "choices": ["routes.json", "routes.yaml", "routes.xml", "routes.php"],
                "answerIndex": 1,
                "explanation": "Le fichier principal est config/routes.yaml en Symfony moderne"
            },
        ],
        "rest": [
            {
                "text": "Quel verbe HTTP est utilisé pour créer une ressource ?",
                "choices": ["GET", "POST", "PUT", "DELETE"],
                "answerIndex": 1,
                "explanation": "POST est utilisé pour créer une nouvelle ressource sur le serveur"
            },
            {
                "text": "Quel code HTTP indique une ressource trouvée ?",
                "choices": ["201", "200", "404", "500"],
                "answerIndex": 1,
                "explanation": "200 OK indique que la requête a réussi et la ressource a été trouvée"
            },
        ],
        "python": [
            {
                "text": "Quel mot-clé est utilisé pour définir une fonction en Python ?",
                "choices": ["function", "func", "def", "define"],
                "answerIndex": 2,
                "explanation": "Le mot-clé 'def' est utilisé pour définir une fonction en Python"
            },
            {
                "text": "Quel module Python est utilisé pour les tâches mathématiques avancées ?",
                "choices": ["math", "calculator", "compute", "numbers"],
                "answerIndex": 0,
                "explanation": "Le module 'math' fournit les fonctions mathématiques avancées en Python"
            },
        ],
        "javascript": [
            {
                "text": "Quel mot-clé crée une variable avec portée bloc en JavaScript ?",
                "choices": ["var", "let", "const", "local"],
                "answerIndex": 1,
                "explanation": "'let' et 'const' créent une portée bloc, 'var' a une portée fonction"
            },
            {
                "text": "Quelle méthode Array filtre les éléments selon une condition ?",
                "choices": ["map", "filter", "reduce", "forEach"],
                "answerIndex": 1,
                "explanation": "La méthode 'filter' crée un nouveau tableau avec les éléments qui passent un test"
            },
        ]
    }
    
    # Chercher un template pour le sujet
    topic_lower = topic.lower()
    matched_templates = []
    for key, temps in templates.items():
        if key in topic_lower:
            matched_templates = temps
            break
    
    # Si pas de template trouvé, utiliser des templates génériques
    if not matched_templates:
        matched_templates = [
            {
                "text": f"Quel est un aspect important de {topic} ?",
                "choices": ["Performance", "Sécurité", "Scalabilité", "Maintenabilité"],
                "answerIndex": 1,
                "explanation": f"La sécurité est un aspect crucial dans {topic}"
            }
        ] * count
    
    # Utiliser et répéter les templates selon le nombre de questions
    for i in range(count):
        q = matched_templates[i % len(matched_templates)].copy()
        # Légèrement modifier pour de la variation
        q["text"] = f"Q{i+1}: {q['text']}"
        questions.append(q)
    
    return {"questions": questions}


def validate_quiz(quiz, expected_count):
    """Valide la structure du quiz généré"""
    if not isinstance(quiz, dict) or "questions" not in quiz:
        raise ValueError("Le quiz doit contenir une clé 'questions'")
    
    questions = quiz["questions"]
    if len(questions) != expected_count:
        raise ValueError(f"Attendu {expected_count} questions, reçu {len(questions)}")
    
    for i, q in enumerate(questions):
        if not q.get("text"):
            raise ValueError(f"Question {i}: 'text' manquant ou vide")
        if not isinstance(q.get("choices"), list) or len(q["choices"]) != 4:
            raise ValueError(f"Question {i}: doit avoir exactement 4 choix")
        if not isinstance(q.get("answerIndex"), int) or q["answerIndex"] not in [0, 1, 2, 3]:
            raise ValueError(f"Question {i}: answerIndex invalide")
        if not q.get("explanation"):
            raise ValueError(f"Question {i}: 'explanation' manquante ou vide")


def generate_quiz(topic, level="intermediaire", count=5, use_ollama=True):
    """Génère un quiz - essaie Ollama d'abord, puis fallback template"""
    if use_ollama:
        try:
            return generate_quiz_with_ollama(topic, level, count)
        except Exception as e:
            print(f"⚠ Ollama non disponible ({e}). Utilisation des templates...")
            return generate_quiz_template(topic, level, count)
    else:
        return generate_quiz_template(topic, level, count)


if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser(description="Génère un quiz en Python (SANS API)")
    parser.add_argument("--topic", required=True, help="Sujet du quiz")
    parser.add_argument("--level", choices=["facile","intermediaire","difficile"], default="intermediaire")
    parser.add_argument("--count", type=int, default=5, help="Nombre de questions")
    parser.add_argument("--template-only", action="store_true", help="Force l'utilisation des templates (pas Ollama)")
    
    args = parser.parse_args()
    quiz = generate_quiz(args.topic, args.level, args.count, use_ollama=not args.template_only)
    print(json.dumps(quiz, ensure_ascii=False, indent=2))


#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Générateur de quiz (OFFLINE, sans API)
- Entrée: titre de chapitre + (optionnel) texte du chapitre
- Sortie: JSON sur stdout (et, en option, fichiers) pour récupération par Symfony

Exemples:
  python quiz_generator.py --title "Symfony 6 - Doctrine" --text-file chapitre.txt --n 12
  python quiz_generator.py --title "Réseaux TCP/IP" --n 10
"""

from __future__ import annotations
import argparse
import json
import random
import re
from dataclasses import dataclass, asdict
from typing import List, Optional, Dict


STOPWORDS_FR = {
    "le","la","les","un","une","des","de","du","d","et","ou","à","a","au","aux",
    "en","dans","sur","pour","par","avec","sans","ce","cet","cette","ces","qui",
    "que","quoi","dont","où","est","sont","être","se","sa","son","ses","leur",
    "leurs","elle","il","ils","elles","nous","vous","je","tu","on","plus","moins",
    "très","trop","peu","car","comme","si","alors","donc","mais","or","ni","pas",
    "ne","aucun","toute","toutes","tout","tous","ainsi","cela","ça"
}


@dataclass
class Question:
    qtype: str                # "mcq" | "tf" | "short"
    question: str
    choices: Optional[List[str]] = None
    answer: str = ""
    explanation: Optional[str] = None
    source: Optional[str] = None


# --- helper functions (same as provided) ----------------------------------

def normalize_text(text: str) -> str:
    text = text.replace("\u00a0", " ")
    return re.sub(r"\s+", " ", text).strip()


def split_sentences(text: str) -> List[str]:
    text = normalize_text(text)
    if not text:
        return []
    parts = re.split(r"(?<=[\.!\?])\s+", text)
    return [p.strip() for p in parts if p.strip()]


def tokenize(text: str) -> List[str]:
    text = text.lower()
    tokens = re.findall(r"[a-zàâäçéèêëîïôöùûüÿñæœ0-9][a-zàâäçéèêëîïôöùûüÿñæœ0-9\-_']*", text)
    out = []
    for t in tokens:
        t = t.strip("'")
        if len(t) < 3:
            continue
        if t in STOPWORDS_FR:
            continue
        out.append(t)
    return out


def extract_keywords(title: str, text: str, top_k: int = 30) -> List[str]:
    freq: Dict[str, int] = {}
    for t in tokenize(title):
        freq[t] = freq.get(t, 0) + 4
    for t in tokenize(text):
        freq[t] = freq.get(t, 0) + 1

    scored = sorted(freq.items(), key=lambda kv: (kv[1], len(kv[0])), reverse=True)
    keywords = [w for w, _ in scored[:top_k]]
    seen = set(); res = []
    for k in keywords:
        if k not in seen:
            seen.add(k);
            res.append(k)
    return res


def pick_definition_sentences(sentences: List[str]) -> List[str]:
    patterns = [
        r"\best\b", r"\bdésigne\b", r"\bcorrespond\b", r"\bpermet\b",
        r"\bse définit\b", r"\bconsiste\b", r"\bprincipe\b", r"\bobjectif\b"
    ]
    defs = []
    for s in sentences:
        low = s.lower()
        if any(re.search(p, low) for p in patterns) and len(s) >= 50:
            defs.append(s)
    return defs


def make_mcq_from_sentence(sentence: str, keywords: List[str], rng: random.Random) -> Optional[Question]:
    low = sentence.lower()
    present = [k for k in keywords if k in low and len(k) >= 4]
    if not present:
        return None
    answer = rng.choice(present)
    masked = re.sub(re.escape(answer), "_____", sentence, count=1, flags=re.IGNORECASE)

    candidates = [k for k in keywords if k != answer and abs(len(k) - len(answer)) <= 3]
    rng.shuffle(candidates)
    distractors = candidates[:3]
    if len(distractors) < 3:
        more = [k for k in keywords if k != answer and k not in distractors]
        rng.shuffle(more)
        distractors += more[: (3 - len(distractors))]

    if len(distractors) < 3:
        return None

    choices = distractors + [answer]
    rng.shuffle(choices)
    return Question(
        qtype="mcq",
        question=f"Complétez la phrase : {masked}",
        choices=choices,
        answer=answer,
        explanation="Le mot manquant correspond à un concept clé du chapitre.",
        source=sentence
    )


def make_tf_from_sentence(sentence: str, keywords: List[str], rng: random.Random) -> Optional[Question]:
    if not sentence or len(sentence) < 50:
        return None
    low = sentence.lower()
    present = [k for k in keywords if k in low and len(k) >= 4]
    if not present:
        return None

    if rng.random() < 0.5:
        return Question(
            qtype="tf",
            question=f"Vrai ou Faux : {sentence}",
            answer="Vrai",
            explanation="Affirmation reprise du texte du chapitre.",
            source=sentence
        )

    target = rng.choice(present)
    repl_pool = [k for k in keywords if k != target and len(k) >= 4]
    if not repl_pool:
        return None
    repl = rng.choice(repl_pool)
    modified = re.sub(re.escape(target), repl, sentence, count=1, flags=re.IGNORECASE)
    return Question(
        qtype="tf",
        question=f"Vrai ou Faux : {modified}",
        answer="Faux",
        explanation=f"Le terme “{repl}” a été substitué à “{target}”.",
        source=sentence
    )


def make_short_questions(title: str, keywords: List[str]) -> List[Question]:
    if not keywords:
        keywords = tokenize(title)[:6]

    qs: List[Question] = []
    for k in keywords[:6]:
        qs.append(Question(
            qtype="short",
            question=f"Définissez : {k}.",
            answer="Réponse attendue : définition + rôle + exemple (selon le cours)."
        ))
    qs.append(Question(
        qtype="short",
        question=f"Citez 2 à 4 objectifs principaux du chapitre « {title} ».",
        answer="Réponse attendue : objectifs clés (selon le cours)."
    ))
    qs.append(Question(
        qtype="short",
        question=f"Donnez un exemple concret d’application lié au chapitre « {title} ».",
        answer="Réponse attendue : exemple + justification (selon le cours)."
    ))
    return qs


def generate_quiz(title: str, text: str, n: int, seed: int) -> List[Question]:
    rng = random.Random(seed)
    text = normalize_text(text)
    sentences = split_sentences(text) if text else []
    keywords = extract_keywords(title, text if text else title)

    questions: List[Question] = []

    if sentences:
        defs = pick_definition_sentences(sentences)
        rng.shuffle(defs)
        pool = defs[: min(30, len(defs))] or sentences

        for s in pool:
            if len(questions) >= n:
                break
            q = make_mcq_from_sentence(s, keywords, rng)
            if q:
                questions.append(q)

        for s in pool:
            if len(questions) >= n:
                break
            q = make_tf_from_sentence(s, keywords, rng)
            if q:
                questions.append(q)

    if len(questions) < n:
        for q in make_short_questions(title, keywords):
            if len(questions) >= n:
                break
            questions.append(q)

    while len(questions) < n and keywords:
        ans = rng.choice(keywords[: min(12, len(keywords))])
        distractors = [k for k in keywords if k != ans]
        rng.shuffle(distractors)
        distractors = distractors[:3]
        if len(distractors) < 3:
            break
        choices = distractors + [ans]
        rng.shuffle(choices)
        questions.append(Question(
            qtype="mcq",
            question=f"Lequel de ces termes est le plus lié au chapitre « {title} » ?",
            choices=choices,
            answer=ans
        ))

    rng.shuffle(questions)
    return questions[:n]


def render_text(questions: List[Question]) -> str:
    out = []
    for i, q in enumerate(questions, 1):
        out.append(f"{i}. [{q.qtype.upper()}] {q.question}")
        if q.choices:
            for j, c in enumerate(q.choices, 1):
                out.append(f"   {j}) {c}")
        out.append(f"   Réponse: {q.answer}")
        if q.explanation:
            out.append(f"   Explication: {q.explanation}")
        out.append("")
    return "\n".join(out)


def main() -> None:
    p = argparse.ArgumentParser(description="Générateur de quiz offline (sans API).")
    p.add_argument("--title", required=True, help="Titre du chapitre (obligatoire).")
    p.add_argument("--text-file", help="Fichier texte du chapitre (optionnel).")
    p.add_argument("--text", default="", help="Texte du chapitre en ligne (optionnel).")
    p.add_argument("--n", type=int, default=10, help="Nombre de questions.")
    p.add_argument("--seed", type=int, default=42, help="Graine aléatoire.")
    p.add_argument("--out", default="quiz.json", help="Sortie JSON.")
    p.add_argument("--out-txt", default="quiz.txt", help="Sortie TXT.")
    args = p.parse_args()

    chapter_text = args.text
    if args.text_file:
        with open(args.text_file, "r", encoding="utf-8") as f:
            chapter_text = f.read()

    questions = generate_quiz(args.title, chapter_text, args.n, args.seed)

    payload = {"title": args.title, "count": len(questions), "questions": [asdict(q) for q in questions]}
    # always write JSON file for debug but also print to stdout
    with open(args.out, "w", encoding="utf-8") as f:
        json.dump(payload, f, ensure_ascii=False, indent=2)

    with open(args.out_txt, "w", encoding="utf-8") as f:
        f.write(render_text(questions))

    # print result JSON to stdout so Symfony can capture it directly
    print(json.dumps(payload, ensure_ascii=False))


if __name__ == "__main__":
    main()

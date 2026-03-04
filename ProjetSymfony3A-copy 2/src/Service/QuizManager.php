<?php

namespace App\Service;

use App\Entity\Quiz;

class QuizManager
{
    public function validate(Quiz $quiz): bool
    {
        $title = trim((string) $quiz->getTitle());

        // Règle 1 : titre valide
        if ($title === '') {
            throw new \InvalidArgumentException('Le titre du quiz est obligatoire');
        }
        if (mb_strlen($title) < 3) {
            throw new \InvalidArgumentException('Le titre du quiz doit contenir au moins 3 caractères');
        }
        if (!preg_match('/^[A-Z]/', $title)) {
            throw new \InvalidArgumentException('Le titre du quiz doit commencer par une majuscule (A-Z)');
        }

        // Règle 2 : au moins une question
        if ($quiz->getQuestions()->count() < 1) {
            throw new \InvalidArgumentException('Le quiz doit contenir au moins 1 question');
        }

        return true;
    }
}
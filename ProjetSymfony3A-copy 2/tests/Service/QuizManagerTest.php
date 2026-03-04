<?php

namespace App\Tests;

use App\Entity\Quiz;
use App\Entity\QuestionQuiz;
use App\Service\QuizManager;
use PHPUnit\Framework\TestCase;

class QuizManagerTest extends TestCase
{
    public function testValidQuiz(): void
    {
        $quiz = new Quiz();
        $quiz->setTitle('Quiz Symfony');

        $question = new QuestionQuiz();
        $quiz->addQuestion($question);

        $manager = new QuizManager();
        $this->assertTrue($manager->validate($quiz));
    }

    public function testQuizWithInvalidTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $quiz = new Quiz();
        $quiz->setTitle('ab'); // invalide (<3 et pas majuscule)

        $question = new QuestionQuiz();
        $quiz->addQuestion($question);

        $manager = new QuizManager();
        $manager->validate($quiz);
    }

    public function testQuizWithoutQuestions(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $quiz = new Quiz();
        $quiz->setTitle('Quiz Symfony');

        $manager = new QuizManager();
        $manager->validate($quiz);
    }
}
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quiz', name: 'quiz_')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(): Response
    {
        return $this->render('quiz/quiz-list.html.twig');
    }

    #[Route('/detail/{id<\d+>}', name: 'detail')]
    public function detail(int $id): Response
    {
        return $this->render('quiz/quiz-detail.html.twig', [
            'quizId' => $id,
        ]);
    }

    #[Route('/added', name: 'added')]
    public function added(): Response
    {
        return $this->render('quiz/quiz-added.html.twig');
    }
}

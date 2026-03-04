<?php

namespace App\Controller\Instructor;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor/quiz')]
#[IsGranted('ROLE_INSTRUCTOR')]
class QuizforController extends AbstractController
{
    #[Route('', name: 'instructor_quiz_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('instructor/quiz/list.html.twig');
    }

    #[Route('/create', name: 'instructor_quiz_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        return $this->render('instructor/quiz/add.html.twig');
    }
}

<?php

namespace App\Controller\Student;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student/clubs')]
class ClubController extends AbstractController
{
    #[Route('', name: 'student_club_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('student/club/list.html.twig');
    }

    #[Route('/join', name: 'student_club_join', methods: ['GET', 'POST'])]
    public function join(): Response
    {
        return $this->render('student/club/add.html.twig');
    }
}

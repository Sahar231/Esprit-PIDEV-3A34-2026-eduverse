<?php

namespace App\Controller\Instructor;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/instructor/clubs')]
class ClubController extends AbstractController
{
    #[Route('', name: 'instructor_club_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('instructor/club/list.html.twig');
    }

    #[Route('/create', name: 'instructor_club_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        return $this->render('instructor/club/add.html.twig');
    }
}

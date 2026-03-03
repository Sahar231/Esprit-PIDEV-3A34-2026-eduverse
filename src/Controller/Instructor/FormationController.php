<?php

namespace App\Controller\Instructor;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/instructor/formations')]
class FormationController extends AbstractController
{
    #[Route('', name: 'instructor_formation_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('instructor/formation/list.html.twig');
    }

    #[Route('/create', name: 'instructor_formation_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        return $this->render('instructor/formation/add.html.twig');
    }
}

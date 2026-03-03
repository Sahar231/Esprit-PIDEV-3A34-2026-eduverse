<?php

namespace App\Controller\Student;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student/formations')]
class FormationController extends AbstractController
{
    #[Route('', name: 'student_formation_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('student/formation/list.html.twig');
    }

    #[Route('/enroll', name: 'student_formation_enroll', methods: ['GET', 'POST'])]
    public function enroll(): Response
    {
        return $this->render('student/formation/add.html.twig');
    }
}

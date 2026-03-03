<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/formations', name: 'formation_')]
class FormationController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(): Response
    {
        return $this->render('formation/formation-list.html.twig');
    }

    #[Route('/detail/{id<\d+>}', name: 'detail')]
    public function detail(int $id): Response
    {
        return $this->render('formation/formation-detail.html.twig', [
            'formationId' => $id,
        ]);
    }

    #[Route('/added', name: 'added')]
    public function added(): Response
    {
        return $this->render('formation/formation-added.html.twig');
    }
}

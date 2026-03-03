<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clubs', name: 'club_')]
class ClubController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(): Response
    {
        return $this->render('club/club-list.html.twig');
    }

    #[Route('/detail/{id<\d+>}', name: 'detail')]
    public function detail(int $id): Response
    {
        return $this->render('club/club-detail.html.twig', [
            'clubId' => $id,
        ]);
    }

    #[Route('/added', name: 'added')]
    public function added(): Response
    {
        return $this->render('club/club-added.html.twig');
    }
}

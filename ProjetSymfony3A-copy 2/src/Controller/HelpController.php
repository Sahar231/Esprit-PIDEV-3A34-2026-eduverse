<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/help', name: 'help_')]
class HelpController extends AbstractController
{
    #[Route('/center', name: 'center')]
    public function center(): Response
    {
        return $this->render('help/help-center.html.twig');
    }

    #[Route('/center/{id<\d+>}', name: 'center_detail')]
    public function centerDetail(int $id): Response
    {
        return $this->render('help/help-center-detail.html.twig', [
            'articleId' => $id,
        ]);
    }
}

<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/clubs')]
class ClubController extends AbstractController
{
    #[Route('', name: 'admin_club_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('admin/club/list.html.twig');
    }

    #[Route('/add', name: 'admin_club_add', methods: ['GET', 'POST'])]
    public function add(): Response
    {
        return $this->render('admin/club/add.html.twig');
    }
}

<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/formations')]
class FormationController extends AbstractController
{
    #[Route('', name: 'admin_formation_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('admin/formation/list.html.twig');
    }

    #[Route('/add', name: 'admin_formation_add', methods: ['GET', 'POST'])]
    public function add(): Response
    {
        return $this->render('admin/formation/add.html.twig');
    }
}

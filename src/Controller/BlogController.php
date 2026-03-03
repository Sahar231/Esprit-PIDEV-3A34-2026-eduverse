<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(): Response
    {
        return $this->render('blog/blog-grid.html.twig');
    }

    #[Route('/grid', name: 'grid')]
    public function grid(): Response
    {
        return $this->render('blog/blog-grid.html.twig');
    }

    #[Route('/masonry', name: 'masonry')]
    public function masonry(): Response
    {
        return $this->render('blog/blog-masonry.html.twig');
    }

    #[Route('/detail/{id<\d+>}', name: 'detail')]
    public function detail(int $id): Response
    {
        return $this->render('blog/blog-detail.html.twig', [
            'blogId' => $id,
        ]);
    }
}

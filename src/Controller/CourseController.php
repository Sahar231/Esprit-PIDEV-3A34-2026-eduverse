<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/courses', name: 'course_')]
class CourseController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(): Response
    {
        return $this->render('course/course-list.html.twig');
    }

    #[Route('/grid', name: 'grid')]
    public function grid(): Response
    {
        return $this->render('course/course-grid.html.twig');
    }

    #[Route('/grid-2', name: 'grid_2')]
    public function grid2(): Response
    {
        return $this->render('course/course-grid-2.html.twig');
    }

    #[Route('/categories', name: 'categories')]
    public function categories(): Response
    {
        return $this->render('course/course-categories.html.twig');
    }

    #[Route('/detail/{id<\d+>}', name: 'detail')]
    public function detail(int $id): Response
    {
        return $this->render('course/course-detail.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/detail-advanced/{id<\d+>}', name: 'detail_advanced')]
    public function detailAdvanced(int $id): Response
    {
        return $this->render('course/course-detail-adv.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/detail-minimal/{id<\d+>}', name: 'detail_minimal')]
    public function detailMinimal(int $id): Response
    {
        return $this->render('course/course-detail-min.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/detail-module/{id<\d+>}', name: 'detail_module')]
    public function detailModule(int $id): Response
    {
        return $this->render('course/course-detail-module.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/video-player/{id<\d+>}', name: 'video_player')]
    public function videoPlayer(int $id): Response
    {
        return $this->render('course/course-video-player.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/list-2', name: 'list_2')]
    public function list2(): Response
    {
        return $this->render('course/course-list-2.html.twig');
    }

    #[Route('/added', name: 'added')]
    public function added(): Response
    {
        return $this->render('course/course-added.html.twig');
    }

    #[Route('/book-class', name: 'book_class')]
    public function bookClass(): Response
    {
        return $this->render('course/book-class.html.twig');
    }
}

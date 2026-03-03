<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/admin-dashboard.html.twig');
    }

    #[Route('/courses', name: 'courses')]
    public function courses(): Response
    {
        return $this->render('admin/admin-course-list.html.twig');
    }

    #[Route('/course-category', name: 'course_category')]
    public function courseCategory(): Response
    {
        return $this->render('admin/admin-course-category.html.twig');
    }

    #[Route('/course-detail', name: 'course_detail')]
    public function courseDetail(): Response
    {
        return $this->render('admin/admin-course-detail.html.twig');
    }

    #[Route('/edit-course/{id<\d+>}', name: 'edit_course')]
    public function editCourse(int $id): Response
    {
        return $this->render('admin/admin-edit-course-detail.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/students', name: 'students')]
    public function students(): Response
    {
        return $this->render('admin/admin-student-list.html.twig');
    }

    #[Route('/instructors', name: 'instructors')]
    public function instructors(): Response
    {
        return $this->render('admin/admin-instructor-list.html.twig');
    }

    #[Route('/instructor/{id<\d+>}', name: 'instructor_detail')]
    public function instructorDetail(int $id): Response
    {
        return $this->render('admin/admin-instructor-detail.html.twig', [
            'instructorId' => $id,
        ]);
    }

    #[Route('/instructor-requests', name: 'instructor_requests')]
    public function instructorRequests(): Response
    {
        return $this->render('admin/admin-instructor-request.html.twig');
    }

    #[Route('/reviews', name: 'reviews')]
    public function reviews(): Response
    {
        return $this->render('admin/admin-review.html.twig');
    }

    #[Route('/earnings', name: 'earnings')]
    public function earnings(): Response
    {
        return $this->render('admin/admin-earning.html.twig');
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('admin/admin-setting.html.twig');
    }

    #[Route('/error', name: 'error_404')]
    public function error404(): Response
    {
        return $this->render('admin/admin-error-404.html.twig');
    }
}

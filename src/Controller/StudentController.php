<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student', name: 'student_')]
class StudentController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('student/student-dashboard.html.twig');
    }

    #[Route('/courses', name: 'courses')]
    public function courses(): Response
    {
        return $this->render('student/student-course-list.html.twig');
    }

    #[Route('/course-resume/{id<\d+>}', name: 'course_resume')]
    public function courseResume(int $id): Response
    {
        return $this->render('student/student-course-resume.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/quiz', name: 'quiz')]
    public function quiz(): Response
    {
        // Redirect legacy static quiz page to the dynamic quiz list
        return $this->redirectToRoute('student_quiz_list');
    }

    #[Route('/bookmarks', name: 'bookmarks')]
    public function bookmarks(): Response
    {
        return $this->render('student/student-bookmark.html.twig');
    }

    #[Route('/subscription', name: 'subscription')]
    public function subscription(): Response
    {
        return $this->render('student/student-subscription.html.twig');
    }

    #[Route('/payment-info', name: 'payment_info')]
    public function paymentInfo(): Response
    {
        return $this->render('student/student-payment-info.html.twig');
    }
}

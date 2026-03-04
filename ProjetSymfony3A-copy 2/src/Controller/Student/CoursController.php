<?php

namespace App\Controller\Student;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use App\Repository\ChapitreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/course', name: 'student_course_')]
#[IsGranted('ROLE_STUDENT')]
class CoursController extends AbstractController
{
    public function __construct(
        private CoursRepository $coursRepository,
        private ChapitreRepository $chapitreRepository,
    ) {}

    /**
     * List all approved courses (read-only for students)
     */
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        $courses = $this->coursRepository->findApproved();

        return $this->render('student/course/list.html.twig', [
            'courses' => $courses,
        ]);
    }

    /**
     * View approved course details with all chapters (read-only)
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Cours $cours): Response
    {
        // Only allow viewing approved courses
        if (!$cours->isApproved()) {
            throw $this->createAccessDeniedException('This course is not available yet.');
        }

        $chapters = $this->chapitreRepository->findByCours($cours);

        return $this->render('student/course/show.html.twig', [
            'cours' => $cours,
            'chapters' => $chapters,
        ]);
    }
}

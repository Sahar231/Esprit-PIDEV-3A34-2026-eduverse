<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor', name: 'instructor_')]
#[IsGranted('ROLE_INSTRUCTOR')]
class InstructorController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(ClubRepository $clubRepository, EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        // Get clubs created by this instructor
        $instructorClubs = $clubRepository->createQueryBuilder('c')
            ->where('c.creator = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();

        // Get events created by this instructor
        $instructorEvents = $eventRepository->createQueryBuilder('e')
            ->where('e.creator = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();

        return $this->render('instructor/instructor-dashboard.html.twig', [
            'instructorClubs' => $instructorClubs,
            'instructorEvents' => $instructorEvents,
        ]);
    }

    #[Route('/list', name: 'list')]
    public function list(): Response
    {
        return $this->render('instructor/instructor-list.html.twig');
    }

    #[Route('/create-course', name: 'create_course')]
    public function createCourse(): Response
    {
        return $this->render('instructor/instructor-create-course.html.twig');
    }

    #[Route('/manage-courses', name: 'manage_courses')]
    public function manageCourses(): Response
    {
        return $this->render('instructor/instructor-manage-course.html.twig');
    }

    #[Route('/quiz', name: 'quiz')]
    public function quiz(): Response
    {
        return $this->render('instructor/instructor-quiz.html.twig');
    }

    #[Route('/reviews', name: 'reviews')]
    public function reviews(): Response
    {
        return $this->render('instructor/instructor-review.html.twig');
    }

    #[Route('/earnings', name: 'earnings')]
    public function earnings(): Response
    {
        return $this->render('instructor/instructor-earning.html.twig');
    }

    #[Route('/payout', name: 'payout')]
    public function payout(): Response
    {
        return $this->render('instructor/instructor-payout.html.twig');
    }

    #[Route('/orders', name: 'orders')]
    public function orders(): Response
    {
        return $this->render('instructor/instructor-order.html.twig');
    }

    #[Route('/students', name: 'students')]
    public function students(): Response
    {
        return $this->render('instructor/instructor-studentlist.html.twig');
    }


    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('instructor/instructor-setting.html.twig');
    }

    #[Route('/delete-account', name: 'delete_account')]
    public function deleteAccount(): Response
    {
        return $this->render('instructor/instructor-delete-account.html.twig');
    }

    #[Route('/{id<\d+>}', name: 'detail')]
    public function detail(int $id): Response
    {
        return $this->render('instructor/instructor-single.html.twig', [
            'instructorId' => $id,
        ]);
    }
}

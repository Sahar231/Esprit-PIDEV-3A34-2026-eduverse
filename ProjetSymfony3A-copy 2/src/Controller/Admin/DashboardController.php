<?php

namespace App\Controller\Admin;

use App\Entity\Formation;
use App\Entity\Quiz;
use App\Entity\Cours;
use App\Entity\Club;
use App\Entity\Event;
use App\Repository\UserRepository;
use App\Repository\FormationRepository;
use App\Repository\CoursRepository;
use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use App\Entity\JoinRequest;
use App\Repository\JoinRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/dashboard')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager, 
        UserRepository $userRepository,
        JoinRequestRepository $joinRequestRepository
    ): Response
    {
        $qb = $entityManager->createQueryBuilder();
        
        // Get pending formations with eager loading of creator
        $pendingFormations = $qb
            ->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.isApproved = :approved')
            ->andWhere('f.isArchived = :archived')
            ->setParameter('approved', false)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();

        // Get all approved formations
        $qb = $entityManager->createQueryBuilder();
        $approvedFormations = $qb
            ->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.isApproved = :approved')
            ->andWhere('f.isArchived = :archived')
            ->setParameter('approved', true)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();

        // Get archived formations
        $qb = $entityManager->createQueryBuilder();
        $archivedFormations = $qb
            ->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.isArchived = :archived')
            ->setParameter('archived', true)
            ->getQuery()
            ->getResult();

        // Get pending quizzes (created by instructors, not auto-approved)
        $qb = $entityManager->createQueryBuilder();
        $pendingQuizzes = $qb
            ->select('q')
            ->from(Quiz::class, 'q')
            ->leftJoin('q.creator', 'c')
            ->addSelect('c')
            ->where('q.isApproved = :approved')
            ->andWhere('q.isArchived = :archived')
            ->setParameter('approved', false)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();

        // Get all approved quizzes
        $qb = $entityManager->createQueryBuilder();
        $approvedQuizzes = $qb
            ->select('q')
            ->from(Quiz::class, 'q')
            ->leftJoin('q.creator', 'c')
            ->addSelect('c')
            ->where('q.isApproved = :approved')
            ->andWhere('q.isArchived = :archived')
            ->setParameter('approved', true)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();

        // Get archived quizzes
        $qb = $entityManager->createQueryBuilder();
        $archivedQuizzes = $qb
            ->select('q')
            ->from(Quiz::class, 'q')
            ->leftJoin('q.creator', 'c')
            ->addSelect('c')
            ->where('q.isArchived = :archived')
            ->setParameter('archived', true)
            ->getQuery()
            ->getResult();

        // Get pending courses
        $qb = $entityManager->createQueryBuilder();
        $pendingCourses = $qb
            ->select('c')
            ->from(Cours::class, 'c')
            ->leftJoin('c.creator', 'u')
            ->addSelect('u')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_PENDING)
            ->getQuery()
            ->getResult();

        // Get all approved courses
        $qb = $entityManager->createQueryBuilder();
        $approvedCourses = $qb
            ->select('c')
            ->from(Cours::class, 'c')
            ->leftJoin('c.creator', 'u')
            ->addSelect('u')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_APPROVED)
            ->getQuery()
            ->getResult();

        // Get refused courses
        $qb = $entityManager->createQueryBuilder();
        $refusedCourses = $qb
            ->select('c')
            ->from(Cours::class, 'c')
            ->leftJoin('c.creator', 'u')
            ->addSelect('u')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_REFUSED)
            ->getQuery()
            ->getResult();

        // Get pending clubs
        $qb = $entityManager->createQueryBuilder();
        $pendingClubs = $qb
            ->select('club')
            ->from(Club::class, 'club')
            ->leftJoin('club.creator', 'clubCreator')
            ->addSelect('clubCreator')
            ->where('club.status = :status')
            ->setParameter('status', Club::STATUS_PENDING)
            ->getQuery()
            ->getResult();

        // Get approved clubs
        $qb = $entityManager->createQueryBuilder();
        $approvedClubs = $qb
            ->select('club')
            ->from(Club::class, 'club')
            ->leftJoin('club.creator', 'clubCreator')
            ->addSelect('clubCreator')
            ->where('club.status = :status')
            ->setParameter('status', Club::STATUS_APPROVED)
            ->getQuery()
            ->getResult();

        // Get rejected clubs
        $qb = $entityManager->createQueryBuilder();
        $rejectedClubs = $qb
            ->select('club')
            ->from(Club::class, 'club')
            ->leftJoin('club.creator', 'clubCreator')
            ->addSelect('clubCreator')
            ->where('club.status = :status')
            ->setParameter('status', Club::STATUS_REJECTED)
            ->getQuery()
            ->getResult();

        // Get pending events
        $qb = $entityManager->createQueryBuilder();
        $pendingEvents = $qb
            ->select('event')
            ->from(Event::class, 'event')
            ->leftJoin('event.creator', 'eventCreator')
            ->leftJoin('event.club', 'eventClub')
            ->addSelect('eventCreator')
            ->addSelect('eventClub')
            ->where('event.status = :status')
            ->setParameter('status', Event::STATUS_PENDING)
            ->getQuery()
            ->getResult();

        // Get approved events
        $qb = $entityManager->createQueryBuilder();
        $approvedEvents = $qb
            ->select('event')
            ->from(Event::class, 'event')
            ->leftJoin('event.creator', 'eventCreator')
            ->leftJoin('event.club', 'eventClub')
            ->addSelect('eventCreator')
            ->addSelect('eventClub')
            ->where('event.status = :status')
            ->setParameter('status', Event::STATUS_APPROVED)
            ->getQuery()
            ->getResult();

        // Get latest 5 pending join requests
        $latestPendingJoinRequests = $joinRequestRepository->createQueryBuilder('jr')
            ->leftJoin('jr.club', 'club')
            ->leftJoin('jr.user', 'user')
            ->addSelect('club', 'user')
            ->where('jr.status = :status')
            ->setParameter('status', JoinRequest::STATUS_PENDING)
            ->orderBy('jr.requestedAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Get count of pending join requests
        $pendingJoinCount = $joinRequestRepository->count(['status' => JoinRequest::STATUS_PENDING]);
        $approvedJoinCount = $joinRequestRepository->count(['status' => JoinRequest::STATUS_APPROVED]);
        $totalJoinCount = $joinRequestRepository->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'pendingFormations' => $pendingFormations,
            'approvedFormations' => $approvedFormations,
            'archivedFormations' => $archivedFormations,
            'pendingCount' => count($pendingFormations),
            'approvedCount' => count($approvedFormations),
            'archivedCount' => count($archivedFormations),
            'pendingQuizzes' => $pendingQuizzes,
            'approvedQuizzes' => $approvedQuizzes,
            'archivedQuizzes' => $archivedQuizzes,
            'pendingQuizzesCount' => count($pendingQuizzes),
            'approvedQuizzesCount' => count($approvedQuizzes),
            'archivedQuizzesCount' => count($archivedQuizzes),
            'pendingCourses' => $pendingCourses,
            'approvedCourses' => $approvedCourses,
            'refusedCourses' => $refusedCourses,
            'pendingCoursesCount' => count($pendingCourses),
            'approvedCoursesCount' => count($approvedCourses),
            'refusedCoursesCount' => count($refusedCourses),
            'pendingClubs' => $pendingClubs,
            'approvedClubs' => $approvedClubs,
            'rejectedClubs' => $rejectedClubs,
            'pendingClubsCount' => count($pendingClubs),
            'approvedClubsCount' => count($approvedClubs),
            'rejectedClubsCount' => count($rejectedClubs),
            'totalClubsCount' => count($pendingClubs) + count($approvedClubs),
            'pendingEvents' => $pendingEvents,
            'approvedEvents' => $approvedEvents,
            'pendingEventsCount' => count($pendingEvents),
            'approvedEventsCount' => count($approvedEvents),
            'totalEventsCount' => count($pendingEvents) + count($approvedEvents),
            'totalStudentsCount' => $userRepository->countByRole('ROLE_STUDENT'),
            'totalInstructorsCount' => $userRepository->countByRole('ROLE_INSTRUCTOR'),
            'pendingStudentsCount' => $userRepository->countPending('ROLE_STUDENT'),
            'pendingInstructorsCount' => $userRepository->countPending('ROLE_INSTRUCTOR'),
            'latestPendingJoinRequests' => $latestPendingJoinRequests,
            'pendingJoinCount' => $pendingJoinCount,
            'approvedJoinCount' => $approvedJoinCount,
            'totalJoinCount' => $totalJoinCount,
        ]);
    }

    #[Route('/approvals/formations', name: 'admin_formations_approval', methods: ['GET'])]
    public function approvalsFormations(EntityManagerInterface $entityManager): Response
    {
        $status = $_GET['status'] ?? 'pending';
        
        $qb = $entityManager->createQueryBuilder();
        
        $formations = match($status) {
            'pending' => $qb
                ->select('f')
                ->from(Formation::class, 'f')
                ->leftJoin('f.creator', 'c')
                ->addSelect('c')
                ->where('f.isApproved = :approved')
                ->andWhere('f.isArchived = :archived')
                ->setParameter('approved', false)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult(),
            'approved' => $qb
                ->select('f')
                ->from(Formation::class, 'f')
                ->leftJoin('f.creator', 'c')
                ->addSelect('c')
                ->where('f.isApproved = :approved')
                ->andWhere('f.isArchived = :archived')
                ->setParameter('approved', true)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult(),
            'archived' => $qb
                ->select('f')
                ->from(Formation::class, 'f')
                ->leftJoin('f.creator', 'c')
                ->addSelect('c')
                ->where('f.isArchived = :archived')
                ->setParameter('archived', true)
                ->getQuery()
                ->getResult(),
            default => $qb
                ->select('f')
                ->from(Formation::class, 'f')
                ->leftJoin('f.creator', 'c')
                ->addSelect('c')
                ->where('f.isApproved = :approved')
                ->andWhere('f.isArchived = :archived')
                ->setParameter('approved', false)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult()
        };

        return $this->render('admin/approvals_formations.html.twig', [
            'formations' => $formations,
            'status' => $status,
        ]);
    }

    #[Route('/approvals/quizzes', name: 'admin_quizzes_approval', methods: ['GET'])]
    public function approvalsQuizzes(EntityManagerInterface $entityManager): Response
    {
        $status = $_GET['status'] ?? 'pending';
        
        $qb = $entityManager->createQueryBuilder();
        
        $quizzes = match($status) {
            'pending' => $qb
                ->select('q')
                ->from(Quiz::class, 'q')
                ->leftJoin('q.creator', 'c')
                ->addSelect('c')
                ->where('q.isApproved = :approved')
                ->andWhere('q.isArchived = :archived')
                ->setParameter('approved', false)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult(),
            'approved' => $qb
                ->select('q')
                ->from(Quiz::class, 'q')
                ->leftJoin('q.creator', 'c')
                ->addSelect('c')
                ->where('q.isApproved = :approved')
                ->andWhere('q.isArchived = :archived')
                ->setParameter('approved', true)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult(),
            'archived' => $qb
                ->select('q')
                ->from(Quiz::class, 'q')
                ->leftJoin('q.creator', 'c')
                ->addSelect('c')
                ->where('q.isArchived = :archived')
                ->setParameter('archived', true)
                ->getQuery()
                ->getResult(),
            default => $qb
                ->select('q')
                ->from(Quiz::class, 'q')
                ->leftJoin('q.creator', 'c')
                ->addSelect('c')
                ->where('q.isApproved = :approved')
                ->andWhere('q.isArchived = :archived')
                ->setParameter('approved', false)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult()
        };

        return $this->render('admin/approvals_quizzes.html.twig', [
            'quizzes' => $quizzes,
            'status' => $status,
        ]);
    }
}

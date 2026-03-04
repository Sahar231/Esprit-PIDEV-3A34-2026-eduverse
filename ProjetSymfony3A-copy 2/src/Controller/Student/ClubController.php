<?php

namespace App\Controller\Student;

use App\Entity\Club;
use App\Entity\JoinRequest;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\JoinRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student/clubs')]
class ClubController extends AbstractController
{
    #[Route('', name: 'student_club_list', methods: ['GET'])]
    public function list(ClubRepository $clubRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $myClubs = $clubRepository->createQueryBuilder('c')
            ->join('c.members', 'm')
            ->where('m.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();

        return $this->render('student/club/list.html.twig', [
            'myClubs' => $myClubs,
        ]);
    }

    #[Route('/join', name: 'student_club_join', methods: ['GET', 'POST'])]
    public function join(): Response
    {
        return $this->render('student/club/add.html.twig');
    }

    #[Route('/{id}', name: 'student_club_show', methods: ['GET'])]
    public function show(int $id, ClubRepository $clubRepository): Response
    {
        $club = $clubRepository->find($id);
        
        if (!$club) {
            throw $this->createNotFoundException('Club not found');
        }

        return $this->render('student/club/show.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/{id}/leave', name: 'student_club_leave', methods: ['POST'])]
    public function leave(int $id, ClubRepository $clubRepository, JoinRequestRepository $joinRequestRepository, EntityManagerInterface $em, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $club = $clubRepository->find($id);
        $user = $this->getUser();
        assert($user instanceof User);

        if (!$club) {
            throw $this->createNotFoundException('Club not found');
        }

        if (!$this->isCsrfTokenValid('leave_club' . $club->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('student_dashboard');
        }

        // Remove user from club members
        $club->removeMember($user);

        // Find and remove the approved join request
        $joinRequest = $joinRequestRepository->findOneBy([
            'club' => $club,
            'user' => $user,
            'status' => JoinRequest::STATUS_APPROVED
        ]);

        if ($joinRequest) {
            $em->remove($joinRequest);
        }

        $em->flush();

        $this->addFlash('success', 'Vous avez quitté le club ' . $club->getName());

        return $this->redirectToRoute('student_dashboard');
    }
}

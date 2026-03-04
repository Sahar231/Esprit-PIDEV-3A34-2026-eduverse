<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\JoinRequest;
use App\Entity\User;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use App\Repository\JoinRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clubs', name: 'club_')]
class ClubController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private JoinRequestRepository $joinRequestRepository,
        private EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        $qb = $this->clubRepository->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Club::STATUS_APPROVED)
            ->leftJoin('c.creator', 'creator')
            ->addSelect('creator');

        // Apply search filter
        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.description LIKE :search OR creator.fullName LIKE :search')
               ->setParameter('search', "%$search%");
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('c.createdAt', 'ASC');
                break;
            case 'name_asc':
                $qb->orderBy('c.name', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('c.name', 'DESC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('c.createdAt', 'DESC');
                break;
        }

        $clubs = $qb->getQuery()->getResult();

        return $this->render('club/club-list.html.twig', [
            'clubs' => $clubs,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/detail/{id}', name: 'detail', methods: ['GET'])]
    public function detail(Club $club): Response
    {
        // Only show approved clubs to public
        if ($club->getStatus() !== Club::STATUS_APPROVED) {
            throw $this->createAccessDeniedException();
        }

        $userJoinStatus = null;
        if ($this->getUser()) {
            $joinRequest = $this->joinRequestRepository->findOneBy([
                'club' => $club,
                'user' => $this->getUser(),
            ]);
            $userJoinStatus = $joinRequest?->getStatus();
        }

        $events = $club->getEvents();
        $approvedEvents = array_filter($events->toArray(), fn($e) => $e->getStatus() === 'APPROVED');

        return $this->render('club/club-detail.html.twig', [
            'club' => $club,
            'events' => $approvedEvents,
            'userJoinStatus' => $userJoinStatus,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STUDENT')] // Students and Instructors can create clubs
    public function create(Request $request): Response
    {
        $club = new Club();
        $form = $this->createForm(ClubType::class, $club);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            assert($user instanceof User);
            $club->setCreator($user);
            $club->setStatus(Club::STATUS_PENDING);

            $this->em->persist($club);
            $this->em->flush();

            $this->addFlash('success', 'Club créé avec succès ! En attente de l\'approbation des administrateurs.');
            return $this->redirectToRoute('club_list');
        }

        return $this->render('club/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function edit(Request $request, Club $club): Response
    {
        // Check permissions: creator and club must be approved
        if ($club->getCreator() !== $this->getUser() || !$club->isApproved()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que les clubs approuvés que vous avez créés.');
        }

        $form = $this->createForm(ClubType::class, $club);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $club->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();
            $this->addFlash('success', 'Club mis à jour avec succès !');
            return $this->redirectToRoute('club_detail', ['id' => $club->getId()]);
        }

        return $this->render('club/edit.html.twig', [
            'club' => $club,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function delete(Request $request, Club $club): Response
    {
        // Check permissions
        if ($club->getCreator() !== $this->getUser() || !$club->isApproved()) {
            throw $this->createAccessDeniedException('You can only delete approved clubs you created.');
        }

        if ($this->isCsrfTokenValid('delete' . $club->getId(), $request->request->get('_token'))) {
            $this->em->remove($club);
            $this->em->flush();
            $this->addFlash('success', 'Club deleted successfully!');
        }

        return $this->redirectToRoute('club_list');
    }

    #[Route('/{id}/join', name: 'join', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function join(Request $request, Club $club): Response
    {
        // Check if user already has a join request
        $existingRequest = $this->joinRequestRepository->findOneBy([
            'club' => $club,
            'user' => $this->getUser(),
        ]);

        if ($existingRequest) {
            $this->addFlash('warning', 'You have already requested to join this club.');
            return $this->redirectToRoute('club_detail', ['id' => $club->getId()]);
        }

        if ($this->isCsrfTokenValid('join' . $club->getId(), $request->request->get('_token'))) {
            $user = $this->getUser();
            assert($user instanceof User);
            $joinRequest = new JoinRequest();
            $joinRequest->setClub($club);
            $joinRequest->setUser($user);
            $joinRequest->setStatus(JoinRequest::STATUS_PENDING);

            $this->em->persist($joinRequest);
            $this->em->flush();

            $this->addFlash('success', 'Join request sent! Awaiting admin approval.');
        }

        return $this->redirectToRoute('club_detail', ['id' => $club->getId()]);
    }
}

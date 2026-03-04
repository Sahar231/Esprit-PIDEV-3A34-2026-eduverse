<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clubs/{clubId}/events', name: 'event_')]
class EventController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private ClubRepository $clubRepository,
        private EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(int $clubId, Request $request): Response
    {
        $club = $this->clubRepository->find($clubId);
        if (!$club || $club->getStatus() !== Club::STATUS_APPROVED) {
            throw $this->createNotFoundException('Club not found or not approved.');
        }

        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_asc');

        $qb = $this->eventRepository->createQueryBuilder('e')
            ->where('e.club = :club')
            ->andWhere('e.status = :status')
            ->setParameter('club', $club)
            ->setParameter('status', Event::STATUS_APPROVED)
            ->leftJoin('e.creator', 'creator')
            ->addSelect('creator');

        // Apply search filter
        if ($search) {
            $qb->andWhere('e.title LIKE :search OR e.description LIKE :search')
               ->setParameter('search', "%$search%");
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('e.eventDate', 'ASC');
                break;
            case 'date_desc':
                $qb->orderBy('e.eventDate', 'DESC');
                break;
            case 'title_asc':
                $qb->orderBy('e.title', 'ASC');
                break;
            case 'title_desc':
                $qb->orderBy('e.title', 'DESC');
                break;
        }

        $events = $qb->getQuery()->getResult();

        return $this->render('event/list.html.twig', [
            'club' => $club,
            'events' => $events,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function create(int $clubId, Request $request): Response
    {
        $club = $this->clubRepository->find($clubId);
        if (!$club) {
            throw $this->createNotFoundException('Club not found.');
        }

        // Only club creator can create events
        if ($club->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Only the club creator can add events.');
        }

        if ($request->isMethod('POST')) {
            $event = new Event();
            $event->setTitle($request->request->get('title'));
            $event->setDescription($request->request->get('description'));
            $event->setLocation($request->request->get('location'));
            $event->setEventDate(new \DateTimeImmutable($request->request->get('eventDate')));
            $event->setClub($club);
            $event->setCreator($this->getUser());
            $event->setStatus(Event::STATUS_PENDING);

            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash('success', 'Event created successfully! Awaiting admin approval.');
            return $this->redirectToRoute('event_list', ['clubId' => $club->getId()]);
        }

        return $this->render('event/create.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(int $clubId, int $id): Response
    {
        $club = $this->clubRepository->find($clubId);
        $event = $this->eventRepository->find($id);

        // Verify entities exist and event belongs to the club
        if (!$club || !$event || $event->getClub() !== $club) {
            throw $this->createNotFoundException('Event or Club not found.');
        }

        // Only show approved events to public
        if ($event->getStatus() !== Event::STATUS_APPROVED) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('event/detail.html.twig', [
            'club' => $club,
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function edit(int $clubId, int $id, Request $request): Response
    {
        $club = $this->clubRepository->find($clubId);
        $event = $this->eventRepository->find($id);

        // Verify entities exist and event belongs to club
        if (!$club || !$event || $event->getClub() !== $club) {
            throw $this->createNotFoundException('Event or Club not found.');
        }

        // Check permissions: creator and event must be approved
        if ($event->getCreator() !== $this->getUser() || !$event->isApproved()) {
            throw $this->createAccessDeniedException('You can only edit approved events you created.');
        }

        if ($request->isMethod('POST')) {
            $event->setTitle($request->request->get('title'));
            $event->setDescription($request->request->get('description'));
            $event->setLocation($request->request->get('location'));
            $event->setEventDate(new \DateTimeImmutable($request->request->get('eventDate')));
            $event->setUpdatedAt(new \DateTimeImmutable());

            $this->em->flush();
            $this->addFlash('success', 'Event updated successfully!');
            return $this->redirectToRoute('event_detail', ['clubId' => $club->getId(), 'id' => $event->getId()]);
        }

        return $this->render('event/edit.html.twig', [
            'club' => $club,
            'event' => $event,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function delete(int $clubId, int $id, Request $request): Response
    {
        $club = $this->clubRepository->find($clubId);
        $event = $this->eventRepository->find($id);

        // Verify entities exist and event belongs to club
        if (!$club || !$event || $event->getClub() !== $club) {
            throw $this->createNotFoundException('Event or Club not found.');
        }

        // Check permissions
        if ($event->getCreator() !== $this->getUser() || !$event->isApproved()) {
            throw $this->createAccessDeniedException('You can only delete approved events you created.');
        }

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $this->em->remove($event);
            $this->em->flush();
            $this->addFlash('success', 'Event deleted successfully!');
        }

        return $this->redirectToRoute('event_list', ['clubId' => $club->getId()]);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Club;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\ClubRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/event', name: 'admin_event_')]
#[IsGranted('ROLE_ADMIN')]
class EventController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private ClubRepository $clubRepository,
        private EntityManagerInterface $em,
        private PdfService $pdfService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');
        $status = $request->query->get('status', '');
        $clubId = $request->query->get('club', '');

        $qb = $this->eventRepository->createQueryBuilder('e')
            ->leftJoin('e.club', 'club')
            ->addSelect('club')
            ->leftJoin('e.creator', 'creator')
            ->addSelect('creator');

        // Filter by status if provided
        if ($status) {
            $qb->andWhere('e.status = :status')
               ->setParameter('status', $status);
        }

        // Filter by club if provided
        if ($clubId) {
            $qb->andWhere('e.club = :club')
               ->setParameter('club', $clubId);
        }

        // Apply search filter
        if ($search) {
            $qb->andWhere('e.title LIKE :search OR e.description LIKE :search OR creator.fullName LIKE :search')
               ->setParameter('search', "%$search%");
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('e.eventDate', 'ASC');
                break;
            case 'title_asc':
                $qb->orderBy('e.title', 'ASC');
                break;
            case 'title_desc':
                $qb->orderBy('e.title', 'DESC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('e.eventDate', 'DESC');
                break;
        }

        $events = $qb->getQuery()->getResult();
        $clubs = $this->clubRepository->findApproved();
        $totalCount = $this->eventRepository->count([]);
        $pendingCount = $this->eventRepository->countPending();
        $approvedCount = $this->eventRepository->countApproved();

        return $this->render('admin/event/list.html.twig', [
            'events' => $events,
            'clubs' => $clubs,
            'search' => $search,
            'sort' => $sort,
            'status' => $status,
            'clubId' => $clubId,
            'totalEvents' => $totalCount,
            'pendingEvents' => $pendingCount,
            'approvedEvents' => $approvedCount,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            assert($user instanceof User);
            $event->setCreator($user);
            $event->setStatus(Event::STATUS_APPROVED);

            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');
            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('admin/event/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/export-list', name: 'export_list', methods: ['GET'])]
    public function exportList(): Response
    {
        $events = $this->eventRepository->findAll();
        return $this->pdfService->generateEventsListPdf($events);
    }

    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('admin/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id<\d+>}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('approve' . $event->getId(), $request->request->get('_token'))) {
            $event->approve();
            $this->em->flush();
            $this->addFlash('success', 'Event approved successfully!');
        }

        return $this->redirectToRoute('admin_event_show', ['id' => $event->getId()]);
    }

    #[Route('/{id<\d+>}/reject', name: 'reject', methods: ['POST'])]
    public function reject(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('reject' . $event->getId(), $request->request->get('_token'))) {
            $event->reject();
            $this->em->flush();
            $this->addFlash('success', 'Event rejected successfully!');
        }

        return $this->redirectToRoute('admin_event_show', ['id' => $event->getId()]);
    }

    #[Route('/{id<\d+>}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $this->em->remove($event);
            $this->em->flush();
            $this->addFlash('success', 'Event deleted successfully!');
        }

        return $this->redirectToRoute('admin_event_list');
    }

    #[Route('/{id<\d+>}/export', name: 'export', methods: ['GET'])]
    public function export(Event $event): Response
    {
        return $this->pdfService->generateEventPdf($event);
    }
}

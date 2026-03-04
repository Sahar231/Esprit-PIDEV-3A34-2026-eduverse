<?php

namespace App\Controller\Instructor;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor/events')]
#[IsGranted('ROLE_INSTRUCTOR')]
class EventController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private ClubRepository $clubRepository,
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'instructor_event_list', methods: ['GET'])]
    public function list(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $events = $eventRepository->findBy(['creator' => $user]);

        return $this->render('instructor/event/list.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/create', name: 'instructor_event_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Get form data
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $eventDateStr = $request->request->get('eventDate');
            $location = $request->request->get('location');
            $clubId = $request->request->get('club');

            // Validate required fields
            if (!$title || !$eventDateStr || !$clubId) {
                $this->addFlash('error', 'Please fill in all required fields.');
                return $this->render('instructor/event/add.html.twig');
            }

            // Get the club
            $club = $this->clubRepository->find($clubId);
            if (!$club) {
                $this->addFlash('error', 'Selected club not found.');
                return $this->render('instructor/event/add.html.twig');
            }

            // Parse the datetime
            try {
                $eventDate = new \DateTimeImmutable($eventDateStr);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Invalid date format.');
                return $this->render('instructor/event/add.html.twig');
            }

            // Create new event
            $user = $this->getUser();
            assert($user instanceof User);
            $event = new Event();
            $event->setTitle($title);
            $event->setDescription($description);
            $event->setEventDate($eventDate);
            $event->setLocation($location);
            $event->setClub($club);
            $event->setCreator($user);

            // Save to database
            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash('success', 'Event created successfully!');
            return $this->redirectToRoute('instructor_event_list');
        }

        // Get all clubs for the dropdown
        $clubs = $this->clubRepository->findAll();

        return $this->render('instructor/event/add.html.twig', [
            'clubs' => $clubs,
        ]);
    }

    #[Route('/{id}', name: 'instructor_event_show', methods: ['GET'])]
    public function show(int $id, EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $event = $eventRepository->findOneBy(['id' => $id, 'creator' => $user]);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('instructor/event/show.html.twig', [
            'event' => $event,
        ]);
    }
}

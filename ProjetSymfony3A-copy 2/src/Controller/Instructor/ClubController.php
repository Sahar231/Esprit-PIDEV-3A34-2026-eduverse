<?php

namespace App\Controller\Instructor;

use App\Entity\Club;
use App\Entity\User;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor/clubs')]
#[IsGranted('ROLE_INSTRUCTOR')]
class ClubController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'instructor_club_list', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->getUser();
        $clubs = $this->clubRepository->findBy(['creator' => $user]);

        return $this->render('instructor/club/list.html.twig', [
            'clubs' => $clubs,
        ]);
    }

    #[Route('/create', name: 'instructor_club_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Get form data
            $name = $request->request->get('clubName');
            $description = $request->request->get('clubDesc');
            $category = $request->request->get('clubCategory');
            $status = $request->request->get('clubStatus', 'active');

            // Validate required fields
            if (!$name || !$description || !$category) {
                $this->addFlash('error', 'Please fill in all required fields.');
                return $this->render('instructor/club/add.html.twig');
            }

            // Create new club
            $club = new Club();
            $club->setName($name);
            $club->setDescription($description);
            $user = $this->getUser();
            assert($user instanceof User);
            $club->setCreator($user);
            $club->setStatus(Club::STATUS_PENDING);

            // Save to database
            $this->em->persist($club);
            $this->em->flush();

            $this->addFlash('success', 'Club created successfully!');
            return $this->redirectToRoute('instructor_club_list');
        }

        return $this->render('instructor/club/add.html.twig');
    }
}

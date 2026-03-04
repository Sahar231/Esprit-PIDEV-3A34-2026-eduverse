<?php

namespace App\Controller\Student;

use App\Entity\Formation;
use App\Entity\User;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/formations')]
#[IsGranted('ROLE_STUDENT')]
class FormationController extends AbstractController
{
    #[Route('', name: 'student_formations', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';
        
        // Get enrolled formations
        $enrolledQb = $entityManager->createQueryBuilder();
        $enrolledQb->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->innerJoin('f.users', 'u')
            ->where('u.id = :userId')
            ->andWhere('f.isArchived = :archived')
            ->setParameter('userId', $user->getId())
            ->setParameter('archived', false);

        // Get available formations (not enrolled, approved, not archived)
        $availableQb = $entityManager->createQueryBuilder();
        $availableQb->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.isApproved = :approved')
            ->andWhere('f.isArchived = :archived')
            ->andWhere('f.id NOT IN (
                SELECT f2.id FROM ' . Formation::class . ' f2
                INNER JOIN f2.users u2
                WHERE u2.id = :userId
            )')
            ->setParameter('approved', true)
            ->setParameter('archived', false)
            ->setParameter('userId', $user->getId());

        // Apply search filter
        if (!empty($search)) {
            $enrolledQb->andWhere('f.title LIKE :search OR f.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
            
            $availableQb->andWhere('f.title LIKE :search OR f.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply sort
        switch ($sort) {
            case 'title_asc':
                $enrolledQb->orderBy('f.title', 'ASC');
                $availableQb->orderBy('f.title', 'ASC');
                break;
            case 'title_desc':
                $enrolledQb->orderBy('f.title', 'DESC');
                $availableQb->orderBy('f.title', 'DESC');
                break;
            case 'date_asc':
                $enrolledQb->orderBy('f.createdAt', 'ASC');
                $availableQb->orderBy('f.createdAt', 'ASC');
                break;
            case 'date_desc':
            default:
                $enrolledQb->orderBy('f.createdAt', 'DESC');
                $availableQb->orderBy('f.createdAt', 'DESC');
                break;
        }

        $enrolledFormations = $enrolledQb->getQuery()->getResult();
        $availableFormations = $availableQb->getQuery()->getResult();

        return $this->render('student/formation/list.html.twig', [
            'enrolledFormations' => $enrolledFormations,
            'availableFormations' => $availableFormations,
            'search' => $search,
            'sort' => $sort
        ]);
    }

    #[Route('/{id}', name: 'student_formation_view', methods: ['GET'])]
    public function view(Formation $formation): Response
    {
        // Prevent viewing archived formations
        if ($formation->isArchived()) {
            $this->addFlash('error', 'This formation has been archived and is no longer available.');
            return $this->redirectToRoute('student_formations');
        }

        return $this->render('student/formation/view.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/enroll', name: 'student_formation_enroll', methods: ['POST'])]
    public function enroll(Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        if (!$user->getFormations()->contains($formation)) {
            $user->addFormation($formation);
            $entityManager->flush();
            $this->addFlash('success', 'Successfully enrolled in ' . $formation->getTitle());
        } else {
            $this->addFlash('warning', 'You are already enrolled in this formation');
        }

        return $this->redirectToRoute('student_formation_view', ['id' => $formation->getId()]);
    }

    #[Route('/{id}/unenroll', name: 'student_formation_unenroll', methods: ['POST'])]
    public function unenroll(Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        if ($user->getFormations()->contains($formation)) {
            $user->removeFormation($formation);
            $entityManager->flush();
            $this->addFlash('success', 'Successfully unenrolled from ' . $formation->getTitle());
        }

        return $this->redirectToRoute('student_formations');
    }
}

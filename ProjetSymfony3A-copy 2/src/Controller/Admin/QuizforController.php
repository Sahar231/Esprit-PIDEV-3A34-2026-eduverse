<?php

namespace App\Controller\Admin;

use App\Entity\Formation;
use App\Entity\Quiz;
use App\Entity\Quizfor;
use App\Form\QuizforType;
use App\Form\QuizType;
use App\Repository\FormationRepository;
use App\Repository\NewQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/quiz')]
#[IsGranted('ROLE_ADMIN')]
class QuizforController extends AbstractController
{
    #[Route('', name: 'admin_quiz_list', methods: ['GET'])]
    public function list(Request $request, NewQuizRepository $quizRepository): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        $qb = $quizRepository->createQueryBuilder('q')
            ->leftJoin('q.creator', 'c')
            ->addSelect('c');

        // Apply search filter
        if ($search) {
            $qb->andWhere('q.title LIKE :search OR q.description LIKE :search')
               ->setParameter('search', "%$search%");
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('q.createdAt', 'ASC');
                break;
            case 'a_z':
                $qb->orderBy('q.title', 'ASC');
                break;
            case 'z_a':
                $qb->orderBy('q.title', 'DESC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('q.createdAt', 'DESC');
                break;
        }

        $quizzes = $qb->getQuery()->getResult();

        return $this->render('admin/quiz/list.html.twig', [
            'quizzes' => $quizzes,
            'search' => $search,
            'sort' => $sort
        ]);
    }

    #[Route('/add', name: 'admin_quiz_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, FormationRepository $formationRepository): Response
    {
        $formationId = $request->query->getInt('formationId', 0);
        $formation = null;
        
        if ($formationId > 0) {
            $formation = $formationRepository->find($formationId);
            if (!$formation) {
                $this->addFlash('error', 'Formation not found');
                return $this->redirectToRoute('admin_formation_list');
            }
        }
        
        $quiz = new Quizfor($formation);
        
        $form = $this->createForm(QuizforType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // calculate total questions based on association (e.g. newly added questions)
            $quiz->setTotalQuestions($quiz->getQuestions()->count());

            $entityManager->persist($quiz);
            $entityManager->flush();

            if ($formation) {
                return $this->redirectToRoute('admin_formation_edit', ['id' => $formation->getId()], Response::HTTP_SEE_OTHER);
            }
            return $this->redirectToRoute('admin_quiz_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/quiz/add.html.twig', [
            'form' => $form,
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/show', name: 'admin_quiz_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $qb = $entityManager->createQueryBuilder();
        $quiz = $qb
            ->select('q')
            ->from(Quizfor::class, 'q')
            ->leftJoin('q.questions', 'questions')
            ->addSelect('questions')
            ->leftJoin('q.Formation', 'f')
            ->addSelect('f')
            ->where('q.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$quiz) {
            $this->addFlash('error', 'Quiz not found');
            return $this->redirectToRoute('admin_quiz_list');
        }

        return $this->render('admin/quiz/show.html.twig', [
            'quiz' => $quiz
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $quiz = $entityManager->getRepository(Quizfor::class)->find($id);

        if (!$quiz) {
            $this->addFlash('error', 'Quiz not found');
            return $this->redirectToRoute('admin_quiz_list');
        }

        $form = $this->createForm(QuizforType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // recalc questions count (in case they were added/removed elsewhere)
            $quiz->setTotalQuestions($quiz->getQuestions()->count());

            $entityManager->flush();
            $this->addFlash('success', 'Quiz updated successfully!');
            
            // Redirect back to formation if quiz belongs to a formation
            if ($quiz->getFormation()) {
                return $this->redirectToRoute('admin_formation_edit', ['id' => $quiz->getFormation()->getId()], Response::HTTP_SEE_OTHER);
            }
            
            return $this->redirectToRoute('admin_quiz_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_quiz_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $quiz = $entityManager->getRepository(Quizfor::class)->find($id);

        if (!$quiz) {
            $this->addFlash('error', 'Quiz not found');
            return $this->redirectToRoute('admin_quiz_list');
        }

        $formation = $quiz->getFormation();
        $entityManager->remove($quiz);
        $entityManager->flush();

        $this->addFlash('success', 'Quiz deleted successfully!');
        
        // Redirect back to formation if quiz belongs to a formation
        if ($formation) {
            return $this->redirectToRoute('admin_formation_edit', ['id' => $formation->getId()]);
        }
        
        return $this->redirectToRoute('admin_quiz_list');
    }

    #[Route('/{id}/approve', name: 'admin_quiz_approve', methods: ['POST'])]
    public function approve(int $id, EntityManagerInterface $entityManager): Response
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);

        if (!$quiz) {
            $this->addFlash('error', 'Quiz not found');
            return $this->redirectToRoute('admin_quizzes_approval', ['status' => 'pending']);
        }

        $quiz->setIsApproved(true);
        $entityManager->flush();

        $this->addFlash('success', "Quiz \"" . $quiz->getTitle() . "\" approved successfully!");
        return $this->redirectToRoute('admin_quizzes_approval', ['status' => 'approved']);
    }

    #[Route('/{id}/disapprove', name: 'admin_quiz_disapprove', methods: ['POST'])]
    public function disapprove(int $id, EntityManagerInterface $entityManager): Response
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);

        if (!$quiz) {
            $this->addFlash('error', 'Quiz not found');
            return $this->redirectToRoute('admin_quizzes_approval', ['status' => 'pending']);
        }

        // Archive the quiz to indicate rejection
        $quiz->setArchived(true);
        $entityManager->flush();

        $this->addFlash('error', "Quiz \"" . $quiz->getTitle() . "\" has been rejected and archived!");
        return $this->redirectToRoute('admin_quizzes_approval', ['status' => 'pending']);
    }
}

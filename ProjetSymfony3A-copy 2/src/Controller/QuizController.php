<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\User;
use App\Form\QuizType;
use App\Repository\NewQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/quiz')]
final class QuizController extends AbstractController
{
    #[Route(name: 'quiz_index', methods: ['GET'])]
    public function index(Request $request, NewQuizRepository $quizRepository): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        $qb = $quizRepository->createQueryBuilder('q')
            ->andWhere('q.isApproved = :approved')
            ->setParameter('approved', true);

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

        return $this->render('quiz/index.html.twig', [
            'quizzes' => $quizzes,
            'search' => $search,
            'sort' => $sort
        ]);
    }

    #[Route('/created', name: 'quiz_created', methods: ['GET'])]
    #[IsGranted('ROLE_INSTRUCTOR')]
    public function created(Request $request, NewQuizRepository $quizRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        $qb = $quizRepository->createQueryBuilder('q')
            ->where('q.creator = :creator')
            ->setParameter('creator', $user);

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
        
        return $this->render('quiz/index.html.twig', [
            'quizzes' => $quizzes,
            'title' => 'My Quizzes',
            'search' => $search,
            'sort' => $sort
        ]);
    }

    #[Route('/pending', name: 'quiz_pending', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function pending(NewQuizRepository $quizRepository): Response
    {
        $quizzes = $quizRepository->findPending();
        
        return $this->render('quiz/index.html.twig', [
            'quizzes' => $quizzes,
            'title' => 'Pending Approval'
        ]);
    }

    #[Route('/new', name: 'quiz_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_INSTRUCTOR')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $quiz = new Quiz();
        $quiz->setCreator($user);
        $quiz->setState('pending');
        $quiz->setIsApproved(false);
        
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quiz);
            $entityManager->flush();

            return $this->redirectToRoute('quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quiz/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        return $this->render('quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/edit', name: 'quiz_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_INSTRUCTOR')]
    public function edit(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        // Ensure instructor can only edit their own quizzes
        $user = $this->getUser();
        assert($user instanceof User);
        if ($quiz->getCreator() !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own quizzes.');
        }

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'quiz_delete', methods: ['POST'])]
    #[IsGranted('ROLE_INSTRUCTOR')]
    public function delete(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        // Ensure instructor can only delete their own quizzes
        if ($quiz->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete your own quizzes.');
        }

        if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quiz);
            $entityManager->flush();
        }

        return $this->redirectToRoute('quiz_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/approve', name: 'quiz_approve', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function approve(Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        $quiz->setIsApproved(true);
        $quiz->setState('approved');
        $entityManager->flush();

        return $this->redirectToRoute('quiz_pending', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/reject', name: 'quiz_reject', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function reject(Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        $quiz->setState('rejected');
        $entityManager->flush();

        return $this->redirectToRoute('quiz_pending', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Quiz;
use App\Entity\Question;
use App\Form\QuizType;
use App\Form\QuestionType;
use App\Repository\QuizRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/quiz')]
class QuizController extends AbstractController
{
    // 1️⃣ Liste tous les quiz avec pagination et recherche
    #[Route('/', name: 'admin_quiz_list', methods: ['GET'])]
    public function list(Request $request, QuizRepository $quizRepository): Response
    {
        // Récupère la recherche et la page
        $searchQuery = $request->query->get('search', '');
        $page = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = 5;
        $offset = ($page - 1) * $itemsPerPage;

        // Créer la requête avec filtre de recherche si applicable
        if ($searchQuery) {
            $queryBuilder = $quizRepository->createQueryBuilder('q')
                ->where('q.title LIKE :search OR q.description LIKE :search')
                ->setParameter('search', '%' . $searchQuery . '%')
                ->orderBy('q.createdAt', 'DESC');
            
            $totalCount = count($queryBuilder->getQuery()->getResult());
            
            $quizzes = $queryBuilder
                ->setFirstResult($offset)
                ->setMaxResults($itemsPerPage)
                ->getQuery()
                ->getResult();
        } else {
            $allQuizzes = $quizRepository->findBy([], ['createdAt' => 'DESC']);
            $totalCount = count($allQuizzes);
            
            $quizzes = array_slice($allQuizzes, $offset, $itemsPerPage);
        }

        // Séparer les quiz par statut pour les statistiques
        $allQuizzes = $quizRepository->findAll();
        
        $pendingQuizzes = array_filter($allQuizzes, function($quiz) {
            return $quiz->getStatus() === 'PENDING';
        });
        
        $approvedQuizzes = array_filter($allQuizzes, function($quiz) {
            return $quiz->getStatus() === 'APPROVED';
        });
        
        $refusedQuizzes = array_filter($allQuizzes, function($quiz) {
            return $quiz->getStatus() === 'REFUSED';
        });
        
        $draftQuizzes = array_filter($allQuizzes, function($quiz) {
            return $quiz->getStatus() === null;
        });

        // Créer l'objet pagination
        $totalPages = ceil($totalCount / $itemsPerPage);
        
        $paginationData = (object)[
            'items' => $quizzes,
            'currentPageNumber' => $page,
            'pageCount' => max(1, $totalPages),
        ];

        return $this->render('admin/quiz/list.html.twig', [
            'pagination' => $paginationData,
            'searchQuery' => $searchQuery,
            'pendingQuizzes' => $pendingQuizzes,
            'approvedQuizzes' => $approvedQuizzes,
            'refusedQuizzes' => $refusedQuizzes,
            'draftQuizzes' => $draftQuizzes,
            'page' => $page,
            'totalCount' => $totalCount,
        ]);
    }

    // 2️⃣ Créer un nouveau quiz
    #[Route('/new', name: 'admin_quiz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quiz);
            $em->flush();

            $this->addFlash('success', 'Quiz créé avec succès');
            return $this->redirectToRoute('admin_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('admin/quiz/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 3️⃣ Éditer un quiz
    #[Route('/{id}/edit', name: 'admin_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(
        Quiz $quiz,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Quiz mis à jour avec succès');
            return $this->redirectToRoute('admin_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('admin/quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }

    // 4️⃣ Supprimer un quiz
    #[Route('/{id}', name: 'admin_quiz_delete', methods: ['POST'])]
    public function delete(
        Quiz $quiz,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $quiz->getId(), $request->request->get('_token'))) {
            $em->remove($quiz);
            $em->flush();

            $this->addFlash('success', 'Quiz supprimé avec succès');
        }

        return $this->redirectToRoute('admin_quiz_list');
    }

    // 5️⃣ Afficher les détails d'un quiz
    #[Route('/{id}/show', name: 'admin_quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        return $this->render('admin/quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    // 6️⃣ Ajouter une question au quiz
    #[Route('/{quizId}/question/new', name: 'admin_question_new', methods: ['GET', 'POST'])]
    public function addQuestion(
        int $quizId,
        Request $request,
        EntityManagerInterface $em,
        QuizRepository $quizRepository
    ): Response {
        $quiz = $quizRepository->find($quizId);
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz non trouvé');
        }

        $question = new Question();
        $question->setQuiz($quiz);
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();

            $this->addFlash('success', 'Question ajoutée avec succès');
            return $this->redirectToRoute('admin_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('admin/quiz/question/new.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }

    // 7️⃣ Éditer une question
    #[Route('/question/{id}/edit', name: 'admin_question_edit', methods: ['GET', 'POST'])]
    public function editQuestion(
        Question $question,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $quiz = $question->getQuiz();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Question mise à jour avec succès');
            return $this->redirectToRoute('admin_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('admin/quiz/question/edit.html.twig', [
            'quiz' => $quiz,
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    // 8️⃣ Supprimer une question
    #[Route('/question/{id}', name: 'admin_question_delete', methods: ['POST'])]
    public function deleteQuestion(
        Question $question,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $quizId = $question->getQuiz()->getId();

        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $em->remove($question);
            $em->flush();

            $this->addFlash('success', 'Question supprimée avec succès');
        }

        return $this->redirectToRoute('admin_quiz_edit', ['id' => $quizId]);
    }

    // 9️⃣ Approuver un quiz en attente
    #[Route('/{id}/approve', name: 'admin_quiz_approve', methods: ['GET'])]
    public function approve(
        Quiz $quiz,
        EntityManagerInterface $em
    ): Response {
        if ($quiz->getStatus() !== 'PENDING') {
            $this->addFlash('warning', 'Ce quiz n\'est pas en attente d\'approbation');
            return $this->redirectToRoute('admin_quiz_list');
        }

        $quiz->setStatus('APPROVED');
        $em->flush();

        $this->addFlash('success', 'Quiz approuvé avec succès!');
        return $this->redirectToRoute('admin_quiz_list');
    }

    // 🔟 Refuser un quiz en attente
    #[Route('/{id}/refuse', name: 'admin_quiz_refuse', methods: ['POST'])]
    public function refuse(
        Quiz $quiz,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($quiz->getStatus() !== 'PENDING') {
            $this->addFlash('warning', 'Ce quiz n\'est pas en attente d\'approbation');
            return $this->redirectToRoute('admin_quiz_list');
        }

        $reason = $request->request->get('reason', 'Aucune raison fournie');
        $quiz->setStatus('REFUSED');
        $quiz->setRejectionReason($reason);
        $em->flush();

        $this->addFlash('success', 'Quiz refusé avec les raisons communiquées à l\'instructeur');
        return $this->redirectToRoute('admin_quiz_list');
    }
}

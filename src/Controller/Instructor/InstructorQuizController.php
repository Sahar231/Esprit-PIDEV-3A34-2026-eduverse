<?php

namespace App\Controller\Instructor;

use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\QuizType;
use App\Form\QuestionType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/instructor/quizzes')]
class InstructorQuizController extends AbstractController
{
    // 1️⃣ Liste de TOUS les quiz avec pagination, recherche et statistiques
    #[Route('/', name: 'instructor_quiz_list', methods: ['GET'])]
    public function list(
        Request $request,
        QuizRepository $quizRepository,
        EntityManagerInterface $em
    ): Response {
        // Récupère le paramètre de recherche
        $searchQuery = $request->query->get('search', '');
        
        // Récupère la page actuelle (par défaut 1)
        $page = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = 5;
        $offset = ($page - 1) * $itemsPerPage;

        // Créer la requête avec filtre de recherche et filtre d'instructeur
        if ($searchQuery) {
            $queryBuilder = $quizRepository->createQueryBuilder('q')
                ->where('q.title LIKE :search')
                ->andWhere('q.instructor = :instructor')
                ->setParameter('search', '%' . $searchQuery . '%')
                ->setParameter('instructor', $this->getUser())
                ->orderBy('q.createdAt', 'DESC');
            
            $totalCount = count($queryBuilder->getQuery()->getResult());
            
            $quizzes = $queryBuilder
                ->setFirstResult($offset)
                ->setMaxResults($itemsPerPage)
                ->getQuery()
                ->getResult();
        } else {
            $allQuizzes = $quizRepository->findBy(['instructor' => $this->getUser()], ['createdAt' => 'DESC']);
            $totalCount = count($allQuizzes);
            
            $quizzes = array_slice($allQuizzes, $offset, $itemsPerPage);
        }

        // Récupérer les quiz de cet instructeur pour les statistiques globales
        $instructorQuizzes = $quizRepository->findBy(['instructor' => $this->getUser()]);
        
        // Calculer les statistiques
        $stats = [
            'totalQuizzes' => count($instructorQuizzes),
            'totalQuestions' => 0,
            'totalAttempts' => 0,
            'averageScore' => 0,
        ];

        foreach ($instructorQuizzes as $quiz) {
            $stats['totalQuestions'] += count($quiz->getQuestions());
        }

        // Créer l'objet pagination
        $totalPages = ceil($totalCount / $itemsPerPage);
        
        $paginationData = (object)[
            'items' => $quizzes,
            'currentPageNumber' => $page,
            'pageCount' => max(1, $totalPages),
        ];

        return $this->render('instructor/quiz/list.html.twig', [
            'pagination' => $paginationData,
            'searchQuery' => $searchQuery,
            'stats' => $stats,
            'page' => $page,
            'totalCount' => $totalCount,
        ]);
    }

    // 2️⃣ Créer un nouveau quiz
    #[Route('/new', name: 'instructor_quiz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $quiz = new Quiz();
        // Set the current instructor as the owner
        $quiz->setInstructor($this->getUser());
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set status to PENDING when creating a new quiz
            $quiz->setStatus('PENDING');
            $quiz->setSubmittedAt(new \DateTime());
            
            $em->persist($quiz);
            $em->flush();

            $this->addFlash('success', 'Quiz créé avec succès! Ajoutez maintenant des questions.');
            return $this->redirectToRoute('instructor_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('instructor/quiz/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'Créer un nouveau Quiz'
        ]);
    }

    // 3️⃣ Éditer un quiz et gérer ses questions
    #[Route('/{id}/edit', name: 'instructor_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(
        Quiz $quiz,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Check if this quiz belongs to the current instructor
        if ($quiz->getInstructor() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce quiz.');
            return $this->redirectToRoute('instructor_quiz_list');
        }

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // When an instructor edits a quiz, mark it as pending for admin review
            $quiz->setStatus('PENDING');
            $quiz->setSubmittedAt(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'Quiz mis à jour avec succès');
            return $this->redirectToRoute('instructor_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('instructor/quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
            'questions' => $quiz->getQuestions(),
        ]);
    }

    // 4️⃣ Ajouter une question au quiz
    #[Route('/{quizId}/question/new', name: 'instructor_question_new', methods: ['GET', 'POST'])]
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
        
        // Check if this quiz belongs to the current instructor
        if ($quiz->getInstructor() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce quiz.');
            return $this->redirectToRoute('instructor_quiz_list');
        }

        $question = new Question();
        $question->setQuiz($quiz);
        
        // Initialiser 4 réponses vides par défaut
        for ($i = 0; $i < 4; $i++) {
            $reponse = new Reponse();
            $reponse->setQuestion($question);
            $question->addReponse($reponse);
        }
        
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();

            $this->addFlash('success', 'Question ajoutée avec succès');
            return $this->redirectToRoute('instructor_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('instructor/quiz/question/new.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }

    // 5️⃣ Éditer une question
    #[Route('/question/{id}/edit', name: 'instructor_question_edit', methods: ['GET', 'POST'])]
    public function editQuestion(
        Question $question,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $quiz = $question->getQuiz();
        
        // Check if this quiz belongs to the current instructor
        if ($quiz->getInstructor() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce quiz.');
            return $this->redirectToRoute('instructor_quiz_list');
        }

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Question mise à jour avec succès');
            return $this->redirectToRoute('instructor_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('instructor/quiz/question/edit.html.twig', [
            'quiz' => $quiz,
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    // 6️⃣ Supprimer une question
    #[Route('/question/{id}', name: 'instructor_question_delete', methods: ['POST'])]
    public function deleteQuestion(
        Question $question,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $quiz = $question->getQuiz();
        $quizId = $quiz->getId();
        
        // Check if this quiz belongs to the current instructor
        if ($quiz->getInstructor() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce quiz.');
            return $this->redirectToRoute('instructor_quiz_list');
        }

        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $em->remove($question);
            $em->flush();
            $this->addFlash('success', 'Question supprimée avec succès');
        }

        return $this->redirectToRoute('instructor_quiz_edit', ['id' => $quizId]);
    }

    // 7️⃣ Voir les détails d'un quiz
    #[Route('/{id}/show', name: 'instructor_quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        // Check if this quiz belongs to the current instructor
        if ($quiz->getInstructor() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce quiz.');
            return $this->redirectToRoute('instructor_quiz_list');
        }

        return $this->render('instructor/quiz/show.html.twig', [
            'quiz' => $quiz,
            'questions' => $quiz->getQuestions(),
        ]);
    }

    // 8️⃣ Supprimer un quiz
    #[Route('/{id}', name: 'instructor_quiz_delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function delete(
        Quiz $quiz,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Check if this quiz belongs to the current instructor
        if ($quiz->getInstructor() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce quiz.');
            return $this->redirectToRoute('instructor_quiz_list');
        }

        if ($this->isCsrfTokenValid('delete' . $quiz->getId(), $request->request->get('_token'))) {
            $em->remove($quiz);
            $em->flush();
            $this->addFlash('success', 'Quiz supprimé avec succès');
        }

        return $this->redirectToRoute('instructor_quiz_list');
    }

    // 9️⃣ Soumettre un quiz pour approbation
    #[Route('/{id}/submit', name: 'instructor_quiz_submit', methods: ['GET'])]
    public function submit(
        Quiz $quiz,
        EntityManagerInterface $em
    ): Response {
        // Vérifier que le quiz a au moins 1 question
        if (count($quiz->getQuestions()) === 0) {
            $this->addFlash('error', 'Vous devez ajouter au moins une question avant de soumettre');
            return $this->redirectToRoute('instructor_quiz_edit', ['id' => $quiz->getId()]);
        }

        // Vérifier que le quiz n'a pas déjà une soumission
        if ($quiz->getStatus() === 'APPROVED') {
            $this->addFlash('warning', 'Ce quiz est déjà approuvé');
            return $this->redirectToRoute('instructor_quiz_list');
        }

        // Soumettre le quiz
        $quiz->setStatus('PENDING');
        $quiz->setSubmittedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'Quiz soumis pour approbation. Un administrateur examinera votre demande.');
        return $this->redirectToRoute('instructor_quiz_list');
    }
}

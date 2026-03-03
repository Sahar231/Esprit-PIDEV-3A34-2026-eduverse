<?php

namespace App\Controller\Student;

use App\Entity\Quiz;
use App\Entity\Reponse;
use App\Entity\QuizSubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/student/quiz')]
class QuizController extends AbstractController
{
    // 1️⃣ Liste des quiz
    #[Route('/', name: 'student_quiz_list', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $search = trim((string)$request->query->get('search', ''));
        $perPage = 5;

        $repo = $em->getRepository(Quiz::class);
        $qb = $repo->createQueryBuilder('q');

        if ($search !== '') {
            $qb->where('q.title LIKE :s OR q.description LIKE :s')
                ->setParameter('s', '%'.$search.'%');
        }

        $qb->orderBy('q.createdAt', 'DESC');

        // total count
        $countQb = clone $qb;
        $countQb->select('COUNT(q.id)');
        $total = (int)$countQb->getQuery()->getSingleScalarResult();

        $qb->setFirstResult(($page - 1) * $perPage)
           ->setMaxResults($perPage);

        $quizzes = $qb->getQuery()->getResult();
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        return $this->render('student/quiz/liste.html.twig', [
            'quizzes' => $quizzes,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $total,
            'searchQuery' => $search,
        ]);
    }

    // 🔷 Voir les statistiques des quiz complétés
    #[Route('/statistics', name: 'student_quiz_statistics', methods: ['GET'])]
    public function statistics(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $user = $this->getUser();
        
        // Récupérer les résultats depuis la session ou la base de données
        $quizStats = [];
        $totalCorrect = 0;
        $totalQuestions = 0;

        if ($user) {
            // Utilisateur authentifié: récupérer depuis la base de données
            $submissionRepo = $em->getRepository(QuizSubmission::class);
            $submissions = $submissionRepo->findByStudent($user);
            
            foreach ($submissions as $submission) {
                $quiz = $submission->getQuiz();
                $quizId = $quiz->getId();
                
                if (!isset($quizStats[$quizId])) {
                    $quizStats[$quizId] = [
                        'quiz' => $quiz,
                        'score' => 0,
                        'total' => 0,
                        'score_percentage' => 0,
                        'submissions' => [],
                    ];
                }
                
                $quizStats[$quizId]['score'] += $submission->getScore();
                $quizStats[$quizId]['total'] += $submission->getTotal();
                $quizStats[$quizId]['submissions'][] = $submission;
                
                $totalCorrect += $submission->getScore();
                $totalQuestions += $submission->getTotal();
            }
        } else {
            // Utilisateur non authentifié: récupérer depuis la session
            $sessionResults = $session->get('quiz_results', []);
            
            foreach ($sessionResults as $quizId => $results) {
                $quiz = $em->getRepository(Quiz::class)->find($quizId);
                if (!$quiz) continue;
                
                $quizStats[$quizId] = [
                    'quiz' => $quiz,
                    'score' => $results['score'],
                    'total' => $results['total'],
                    'score_percentage' => $results['total'] > 0 ? round(($results['score'] / $results['total']) * 100) : 0,
                    'submissions' => [
                        [
                            'score' => $results['score'],
                            'total' => $results['total'],
                            'submittedAt' => new \DateTime($results['submitted_at'] ?? 'now'),
                        ]
                    ],
                ];
                
                $totalCorrect += $results['score'];
                $totalQuestions += $results['total'];
            }
        }
        
        // Calculer les pourcentages pour chaque quiz
        foreach ($quizStats as &$stat) {
            if ($stat['total'] > 0) {
                $stat['score_percentage'] = round(($stat['score'] / $stat['total']) * 100);
            }
        }
        
        $averagePercentage = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100) : 0;
        
        return $this->render('student/quiz/statistics.html.twig', [
            'quizStats' => $quizStats,
            'totalCorrect' => $totalCorrect,
            'totalQuestions' => $totalQuestions,
            'averagePercentage' => $averagePercentage,
        ]);
    }

    // 2️⃣ Afficher le quiz
    #[Route('/{id}', name: 'student_quiz_take', methods: ['GET'])]
    public function take(Quiz $quiz): Response
    {
        return $this->render('student/quiz/take.html.twig', [
            'quiz' => $quiz,
            'questions' => $quiz->getQuestions(),
        ]);
    }

    // 3️⃣ Soumettre le quiz
    #[Route('/{id}/submit', name: 'student_quiz_submit', methods: ['POST'])]
    public function submit(
        Quiz $quiz,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $session = $request->getSession();
        $user = $this->getUser();

        // Get raw request parameters (bypasses Symfony's non-scalar filtering)
        $allParams = $request->request->all();
        $answers = [];
        
        // Extract answers from all parameters
        foreach ($allParams as $key => $value) {
            if (strpos($key, 'answer_') === 0) {
                $questionId = str_replace('answer_', '', $key);
                $answers[$questionId] = $value;
            }
        }
        
        $score = 0;
        $results = [];

        foreach ($quiz->getQuestions() as $question) {
            $studentAnswer = $answers[$question->getId()] ?? null;

            if ($studentAnswer === null || $studentAnswer === '') {
                $results[$question->getId()] = [
                    'question' => $question,
                    'studentAnswer' => null,
                    'correct' => false
                ];
                continue;
            }

            // Find the correct answer from reponses
            $correctReponse = null;
            foreach ($question->getReponses() as $reponse) {
                if ($reponse->isCorrect()) {
                    $correctReponse = $reponse;
                    break;
                }
            }

            if (!$correctReponse) {
                // No correct answer defined
                continue;
            }

            // Compare case-insensitive for flexibility
            $isCorrect = strtolower(trim((string)$studentAnswer)) === strtolower(trim((string)$correctReponse->getContent()));

            if ($isCorrect) {
                $score++;
            }

            // Store result for display
            $results[$question->getId()] = [
                'question' => $question,
                'studentAnswer' => $studentAnswer,
                'correct' => $isCorrect
            ];
        }

        // Calculate percentage
        $total = count($quiz->getQuestions());
        $percentage = $total > 0 ? round(($score / $total) * 100) : 0;

        // Save submission
        if ($user) {
            // Utilisateur authentifié: sauvegarder en base de données
            $submission = new QuizSubmission();
            $submission->setQuiz($quiz);
            $submission->setStudent($user);
            $submission->setScore($score);
            $submission->setTotal($total);
            $submission->setAnswers($answers);
            $submission->setSubmittedAt(new \DateTime());
            
            $em->persist($submission);
            $em->flush();

            return $this->render('student/quiz/result_new.html.twig', [
                'quiz' => $quiz,
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'results' => $results,
                'submission' => $submission,
            ]);
        } else {
            // Utilisateur non authentifié: sauvegarder en session
            $sessionResults = $session->get('quiz_results', []);
            $sessionResults[$quiz->getId()] = [
                'score' => $score,
                'total' => $total,
                'answers' => $answers,
                'submitted_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ];
            $session->set('quiz_results', $sessionResults);

            return $this->render('student/quiz/result_new.html.twig', [
                'quiz' => $quiz,
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'results' => $results,
                'submission' => null,
            ]);
        }
    }

    // 4️⃣ Télécharger le PDF du résultat du quiz
    #[Route('/submission/{submissionId}/pdf', name: 'student_attempt_result_pdf', methods: ['GET'])]
    public function downloadResultPdf(
        int $submissionId,
        EntityManagerInterface $em
    ): Response {
        // Sécurité: vérifier que l'utilisateur est authentifié
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour télécharger votre résultat.');
        }

        // Récupérer la soumission
        $submission = $em->getRepository(QuizSubmission::class)->find($submissionId);
        if (!$submission) {
            throw $this->createNotFoundException('Soumission non trouvée.');
        }

        // Vérifier que la soumission appartient à l'utilisateur connecté
        if ($submission->getStudent()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce résultat.');
        }

        // Récupérer les données nécessaires
        $quiz = $submission->getQuiz();
        $score = $submission->getScore();
        $total = $submission->getTotal();
        $percentage = $total > 0 ? round(($score / $total) * 100) : 0;
        $submittedAt = $submission->getSubmittedAt();
        $answers = $submission->getAnswers();

        // Construire les détails des réponses
        $resultDetails = [];
        foreach ($quiz->getQuestions() as $question) {
            $studentAnswer = $answers[$question->getId()] ?? null;
            
            // Trouver la bonne réponse
            $correctReponse = null;
            foreach ($question->getReponses() as $reponse) {
                if ($reponse->isCorrect()) {
                    $correctReponse = $reponse;
                    break;
                }
            }

            // Vérifier si la réponse est correcte
            $isCorrect = false;
            if ($studentAnswer && $correctReponse) {
                $isCorrect = strtolower(trim((string)$studentAnswer)) === 
                             strtolower(trim((string)$correctReponse->getContent()));
            }

            $resultDetails[] = [
                'question' => $question->getText(),
                'studentAnswer' => $studentAnswer ?? 'Non répondu',
                'correctAnswer' => $correctReponse ? $correctReponse->getContent() : 'N/A',
                'isCorrect' => $isCorrect,
            ];
        }

        // Rendre le template Twig pour PDF
        $html = $this->renderView('pdf/student_result.html.twig', [
            'quiz' => $quiz,
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'submittedAt' => $submittedAt,
            'resultDetails' => $resultDetails,
            'student' => $this->getUser(),
        ]);

        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le nom du fichier
        $filename = sprintf('resultat-quiz-%d.pdf', $submissionId);

        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }
}

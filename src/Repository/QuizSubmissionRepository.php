<?php

namespace App\Repository;

use App\Entity\QuizSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizSubmission>
 */
class QuizSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizSubmission::class);
    }

    /**
     * Find all submissions for a specific student
     */
    public function findByStudent($student): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.student = :student')
            ->setParameter('student', $student)
            ->orderBy('s.submittedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find submissions for a specific quiz and student
     */
    public function findByQuizAndStudent($quiz, $student)
    {
        return $this->createQueryBuilder('s')
            ->where('s.quiz = :quiz')
            ->andWhere('s.student = :student')
            ->setParameter('quiz', $quiz)
            ->setParameter('student', $student)
            ->orderBy('s.submittedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get statistics for a student
     */
    public function getStudentStatistics($student): array
    {
        $submissions = $this->findByStudent($student);
        
        $stats = [
            'totalSubmissions' => count($submissions),
            'averageScore' => 0,
            'submissions' => [],
        ];

        $totalScore = 0;
        $totalQuestions = 0;

        foreach ($submissions as $submission) {
            $stats['submissions'][] = [
                'quiz' => $submission->getQuiz(),
                'score' => $submission->getScore(),
                'total' => $submission->getTotal(),
                'percentage' => $submission->getPercentage(),
                'submittedAt' => $submission->getSubmittedAt(),
            ];

            $totalScore += $submission->getScore();
            $totalQuestions += $submission->getTotal();
        }

        if ($totalQuestions > 0) {
            $stats['averageScore'] = round(($totalScore / $totalQuestions) * 100);
        }

        return $stats;
    }
}

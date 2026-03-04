<?php

namespace App\Repository;

use App\Entity\QuestionQuiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuestionQuiz>
 *
 * @method QuestionQuiz|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuestionQuiz|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuestionQuiz[]    findAll()
 * @method QuestionQuiz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionQuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionQuiz::class);
    }
}

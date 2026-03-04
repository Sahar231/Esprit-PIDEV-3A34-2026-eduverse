<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Quiz;
use App\Entity\Resultat;
use App\Entity\Club;
use App\Entity\User;
use App\Repository\CertificateRepository;
use App\Repository\FormationRepository;
use App\Repository\NewQuizRepository;
use App\Repository\ResultatRepository;
use App\Repository\WalletRepository;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\FormationType;

#[Route('/student', name: 'student_')]
#[IsGranted('ROLE_STUDENT')]
class StudentController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(FormationRepository $formationRepository, ClubRepository $clubRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        // Filter enrolled formations to exclude archived ones
        $enrolledFormations = $user->getFormations()->filter(function (Formation $formation) {
            return !$formation->isArchived();
        });

        // Get clubs where the user is either a member OR the creator (approved only)
        $myClubs = $clubRepository->createQueryBuilder('c')
            ->leftJoin('c.members', 'm')
            ->where('m.id = :userId OR c.creator = :user')
            ->andWhere('c.status = :status')
            ->setParameter('userId', $user->getId())
            ->setParameter('user', $user)
            ->setParameter('status', Club::STATUS_APPROVED)
            ->getQuery()
            ->getResult();

        return $this->render('student/student-dashboard.html.twig', [
            'enrolledFormations' => $enrolledFormations,
            'availableFormations' => $formationRepository->findApprovedAndNotArchived(),
            'enrollmentCount' => count($enrolledFormations),
            'myClubs' => $myClubs,
        ]);
    }

    #[Route('/formations', name: 'formations')]
    public function formations(FormationRepository $formationRepository, Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        // Filter enrolled formations to exclude archived ones
        $enrolledFormations = $user->getFormations()->filter(function (Formation $formation) {
            return !$formation->isArchived();
        });

        // Apply search filter if needed
        if (!empty($search)) {
            $enrolledFormations = $enrolledFormations->filter(function (Formation $formation) use ($search) {
                $lowerSearch = strtolower($search);
                return stripos($formation->getTitle(), $search) !== false || 
                       stripos($formation->getDescription(), $search) !== false;
            });
        }

        $availableFormations = $formationRepository->findApprovedAndNotArchived();

        // Apply search filter to available formations
        if (!empty($search)) {
            $availableFormations = array_filter($availableFormations, function (Formation $formation) use ($search) {
                return stripos($formation->getTitle(), $search) !== false || 
                       stripos($formation->getDescription(), $search) !== false;
            });
        }

        // Apply sort
        $sortCallback = function ($a, $b) use ($sort) {
            switch ($sort) {
                case 'title_asc':
                    return strcmp($a->getTitle(), $b->getTitle());
                case 'title_desc':
                    return strcmp($b->getTitle(), $a->getTitle());
                case 'date_asc':
                    return $a->getCreatedAt() <=> $b->getCreatedAt();
                case 'date_desc':
                default:
                    return $b->getCreatedAt() <=> $a->getCreatedAt();
            }
        };

        $enrolledArray = $enrolledFormations->toArray();
        usort($enrolledArray, $sortCallback);
        usort($availableFormations, $sortCallback);

        return $this->render('student/formation/list.html.twig', [
            'enrolledFormations' => $enrolledArray,
            'availableFormations' => $availableFormations,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/formation/{id}', name: 'formation_view')]
    public function viewFormation(Formation $formation, CertificateRepository $certificateRepository): Response
    {
        // Prevent viewing archived formations
        if ($formation->isArchived()) {
            $this->addFlash('error', 'This formation has been archived and is no longer available.');
            return $this->redirectToRoute('student_formations');
        }

        $user = $this->getUser();
        assert($user instanceof User);
        
        // Get all passed quiz IDs for this user in this formation
        $passedQuizIds = [];
        foreach ($formation->getQuizzes() as $quiz) {
            $certificate = $certificateRepository->findByUserAndQuiz($user, $quiz);
            if ($certificate) {
                $passedQuizIds[] = $quiz->getId();
            }
        }

        return $this->render('student/formation/view.html.twig', [
            'formation' => $formation,
            'passedQuizIds' => $passedQuizIds,
        ]);
    }

    #[Route('/formation/{id}/enroll', name: 'formation_enroll', methods: ['POST'])]
    public function enrollFormation(Formation $formation, EntityManagerInterface $entityManager, WalletRepository $walletRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        // Check if already enrolled
        if ($user->getFormations()->contains($formation)) {
            $this->addFlash('warning', 'You are already enrolled in this formation');
            return $this->redirectToRoute('student_formation_view', ['id' => $formation->getId()]);
        }

        // Check if formation has a price
        if ($formation->getPrice() && $formation->getPrice() > 0) {
            // Formation is paid - need to process payment from wallet
            $wallet = $walletRepository->findByUser($user);

            // Check if student has a wallet
            if (!$wallet) {
                $this->addFlash('error', 'You need to create a wallet before purchasing formations. Please create your wallet first to get your welcome bonus.');
                return $this->redirectToRoute('student_wallet_create');
            }

            // Check if wallet has sufficient balance
            if ($wallet->getBalance() < $formation->getPrice()) {
                $insufficientAmount = $formation->getPrice() - $wallet->getBalance();
                $this->addFlash(
                    'error', 
                    sprintf(
                        'Insufficient balance! You need %.2f more credits. Current balance: %.2f credits. Formation price: %.2f credits.',
                        $insufficientAmount,
                        $wallet->getBalance(),
                        $formation->getPrice()
                    )
                );
                return $this->redirectToRoute('student_formation_view', ['id' => $formation->getId()]);
            }

            // Deduct from wallet and enroll
            $wallet->deductBalance($formation->getPrice());
            $user->addFormation($formation);
            $entityManager->flush();

            $this->addFlash(
                'success', 
                sprintf(
                    'Successfully purchased and enrolled in %s! %.2f credits have been deducted from your wallet. New balance: %.2f credits.',
                    $formation->getTitle(),
                    $formation->getPrice(),
                    $wallet->getBalance()
                )
            );
        } else {
            // Free formation - just enroll
            $user->addFormation($formation);
            $entityManager->flush();
            $this->addFlash('success', 'Successfully enrolled in ' . $formation->getTitle());
        }

        return $this->redirectToRoute('student_formation_view', ['id' => $formation->getId()]);
    }

    #[Route('/formation/{id}/unenroll', name: 'formation_unenroll', methods: ['POST'])]
    public function unenrollFormation(Formation $formation, EntityManagerInterface $entityManager): Response
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

    #[Route('/courses', name: 'courses')]
    public function courses(): Response
    {
        return $this->render('student/student-course-list.html.twig');
    }

    #[Route('/course-resume/{id<\d+>}', name: 'course_resume')]
    public function courseResume(int $id): Response
    {
        return $this->render('student/student-course-resume.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/quiz', name: 'quiz_available')]
    public function quizzes(NewQuizRepository $quizRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        
        // Get all approved quizzes
        $availableQuizzes = $quizRepository->findApproved();
        
        // Get quizzes user has already taken (via Resultat)
        $takenQuizzes = $user->getResultats()->map(fn($result) => $result->getQuiz())->toArray();
        
        return $this->render('student/quiz-available.html.twig', [
            'availableQuizzes' => $availableQuizzes,
            'takenQuizzes' => $takenQuizzes,
        ]);
    }

    #[Route('/quiz/my-result', name: 'quiz_results')]
    public function myQuizResults(ResultatRepository $resultatRepository): Response
    {
        $user = $this->getUser();
        $results = $resultatRepository->findBy(['student' => $user], ['createdAt' => 'DESC']);
        
        return $this->render('student/quiz-results.html.twig', [
            'results' => $results,
        ]);
    }

    #[Route('/quiz/{id<\d+>}/start', name: 'quiz_start')]
    public function startQuiz(Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Check if user has already taken this quiz
        $existingResult = $entityManager->getRepository(Resultat::class)->findOneBy([
            'quiz' => $quiz,
            'student' => $user
        ]);

        if ($existingResult) {
            $this->addFlash('warning', 'You have already completed this quiz.');
            return $this->redirectToRoute('student_quiz_available');
        }

        return $this->render('student/quiz/add.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/bookmarks', name: 'bookmarks')]
    public function bookmarks(): Response
    {
        return $this->render('student/student-bookmark.html.twig');
    }

    #[Route('/subscription', name: 'subscription')]
    public function subscription(): Response
    {
        return $this->render('student/student-subscription.html.twig');
    }

    #[Route('/payment-info', name: 'payment_info')]
    public function paymentInfo(): Response
    {
        return $this->render('student/student-payment-info.html.twig');
    }
}

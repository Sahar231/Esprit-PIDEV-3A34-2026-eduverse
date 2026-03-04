<?php

namespace App\Controller\Instructor;

use App\Entity\Formation;
use App\Entity\Quizfor;
use App\Entity\Question;
use App\Entity\Ressources;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor/formations')]
#[IsGranted('ROLE_INSTRUCTOR')]
class FormationController extends AbstractController
{
    #[Route('', name: 'instructor_formation_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';
        
        $qb = $entityManager->createQueryBuilder();
        $qb->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.creator = :creator')
            ->setParameter('creator', $user);

        // Search filter
        if (!empty($search)) {
            $qb->andWhere('f.title LIKE :search OR f.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Sort options
        switch ($sort) {
            case 'title_asc':
                $qb->orderBy('f.title', 'ASC');
                break;
            case 'title_desc':
                $qb->orderBy('f.title', 'DESC');
                break;
            case 'date_asc':
                $qb->orderBy('f.createdAt', 'ASC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('f.createdAt', 'DESC');
                break;
        }

        $formations = $qb->getQuery()->getResult();

        return $this->render('instructor/formation/list.html.twig', [
            'formations' => $formations,
            'search' => $search,
            'sort' => $sort
        ]);
    }

    #[Route('/create', name: 'instructor_formation_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $duration = $request->request->get('duration');
            $level = $request->request->get('level');
            $price = $request->request->get('price');

            // Validation errors array
            $errors = [];

            // Validate title - first letter must be uppercase
            if (empty($title)) {
                $errors[] = 'Formation title is required.';
            } elseif (!ctype_upper($title[0])) {
                $errors[] = 'Formation title must start with an uppercase letter.';
            }

            // Validate description - at least 4 words
            if (empty($description)) {
                $errors[] = 'Formation description is required.';
            } else {
                $wordCount = str_word_count($description);
                if ($wordCount < 4) {
                    $errors[] = 'Formation description must contain at least 4 words. Current: ' . $wordCount . ' word(s).';
                }
            }

            // Validate duration
            if (empty($duration) || !is_numeric($duration)) {
                $errors[] = 'Formation duration is required and must be a number.';
            }

            // Validate level
            if (empty($level)) {
                $errors[] = 'Formation level is required.';
            }

            // Validate price
            if ($price === '' || $price === null) {
                $errors[] = 'Formation price is required.';
            } elseif (!is_numeric($price) || (float)$price < 0) {
                $errors[] = 'Formation price must be a valid number (0 or greater).';
            }

            // Validate quizzes and their score distributions
            /** @var array<int, mixed> $quizzes */
            $quizzes = $request->request->all()['quiz'] ?? [];
            $quizValidationErrors = $this->validateQuizzes($quizzes);
            $errors = array_merge($errors, $quizValidationErrors);

            // If there are validation errors, display them
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->redirectToRoute('instructor_formation_create');
            }

            // All validations passed, create the formation
            $formation = new Formation();
            $formation->setTitle($title);
            $formation->setDescription($description);
            $formation->setContent($description);
            $formation->setPrice((float)$price);
            $formation->setDuration((int)$duration);

            // Handle file upload
            $uploadedFile = $request->files->get('supportFile');
            if ($uploadedFile) {
                $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/formations';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                try {
                    $uploadedFile->move($uploadDir, $fileName);
                    $formation->setSupportFile('/uploads/formations/' . $fileName);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'File upload failed: ' . $e->getMessage());
                    return $this->redirectToRoute('instructor_formation_create');
                }
            }

            // Set creator and associate current instructor with formation
            $user = $this->getUser();
            assert($user instanceof User);
            // assertion ensures $user is not null
            $formation->setCreator($user);
            $formation->addUser($user);

            $entityManager->persist($formation);
            $entityManager->flush();

            // Handle Quizzes and Questions
            foreach ($quizzes as $quizData) {
                if (!is_array($quizData) || empty($quizData['title'])) {
                    continue;
                }

                $quiz = new Quizfor($formation);
                $quiz->setTitle($quizData['title']);
                $quiz->setDescription($quizData['description'] ?? '');
                $quiz->setCategory($quizData['category'] ?? $title);
                // PHPStan expects string for scores (entity setters store strings)
                $quiz->setTotalScore((string)($quizData['total_score'] ?? 100));
                $quiz->setPassScore((string)($quizData['pass_score'] ?? 60));
                $quiz->setDifficulty('medium'); // Set default difficulty

                $questions = $quizData['questions'] ?? [];
                $totalQuestions = 0;
                $totalScore = 0;

                foreach ($questions as $questionData) {
                    if (!is_array($questionData) || empty($questionData['question'])) {
                        continue;
                    }

                    $questionScore = (string)($questionData['score'] ?? 1);
                    $question = new Question();
                    $question->setQuestion($questionData['question']);
                    $question->setScore($questionScore);
                    $question->setType('qcm');
                    $question->setQuiz($quiz);

                    // Handle choices and correct answer
                    $choices = $questionData['choice'] ?? [];
                    $correctIndex = (int)($questionData['correct'] ?? 0);

                    if (!empty($choices) && isset($choices[$correctIndex])) {
                        $question->setReply($choices[$correctIndex]); // Store the correct answer text
                        $question->setChoices($choices); // Store all choices as JSON array
                    }

                    $entityManager->persist($question);
                    $totalQuestions++;
                    $totalScore += $questionScore;
                }

                $quiz->setTotalQuestions($totalQuestions);
                $quiz->setTotalScore((string)$totalScore);
                $entityManager->persist($quiz);
            }

            $entityManager->flush();

            // Handle Resources
            $resources = $request->request->all()['resource'] ?? [];
            foreach ($resources as $resourceData) {
                if (!is_array($resourceData) || empty($resourceData['title'])) {
                    continue;
                }

                $resource = new Ressources();
                $resource->setTitle($resourceData['title']);
                $resource->setDescription($resourceData['description'] ?? '');
                $resource->setType($resourceData['type'] ?? 'other');
                $resource->setUrl($resourceData['url'] ?? null);
                $resource->setCost($resourceData['cost'] ?? null);
                $resource->setFormation($formation);

                $entityManager->persist($resource);
            }

            $entityManager->flush();

            $this->addFlash('success', "Formation \"$title\" created successfully with " . count($quizzes) . ' quiz(zes) and ' . count($resources) . ' resource(s)! It will be reviewed by admin for approval.');
            return $this->redirectToRoute('instructor_formation_list');
        }

        return $this->render('instructor/formation/add.html.twig');
    }

    #[Route('/{id}', name: 'instructor_formation_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $qb = $entityManager->createQueryBuilder();
        $formation = $qb
            ->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('instructor_formation_list');
        }

        // Check if the current user is the creator
        if ($formation->getCreator()?->getId() !== $user->getId()) {
            $this->addFlash('error', 'You can only view your own formations');
            return $this->redirectToRoute('instructor_formation_list');
        }

        return $this->render('instructor/formation/show.html.twig', [
            'formation' => $formation
        ]);
    }

    /**
     * Validate quiz structure and score distributions
     */
    /**
    * @param array<int, mixed> $quizzes
    * @return array<int,string>
     */
    private function validateQuizzes(array $quizzes): array
    {
        $errors = [];
        
        foreach ($quizzes as $quizIndex => $quizData) {
            if (!is_array($quizData) || empty($quizData['title'])) {
                continue;
            }

            $quizNumber = $quizIndex + 1;

            // Validate quiz title
            if (empty($quizData['title'])) {
                $errors[] = "Quiz $quizNumber: Title is required.";
            }

            // Validate total score
            if (empty($quizData['total_score']) || !is_numeric($quizData['total_score'])) {
                $errors[] = "Quiz $quizNumber: Total score is required and must be a number.";
                continue;
            }

            $totalQuizScore = (float)$quizData['total_score'];

            // Validate questions and calculate their total score
            $questions = $quizData['questions'] ?? [];
            $questionScoresSum = 0;
            $questionCount = 0;

            foreach ($questions as $questionIndex => $questionData) {
                if (!is_array($questionData) || empty($questionData['question'])) {
                    continue;
                }

                $questionNumber = $questionIndex + 1;

                // Validate question text
                if (empty($questionData['question'])) {
                    $errors[] = "Quiz $quizNumber, Question $questionNumber: Question text is required.";
                }

                // Validate question score
                if (empty($questionData['score']) || !is_numeric($questionData['score'])) {
                    $errors[] = "Quiz $quizNumber, Question $questionNumber: Question score is required and must be a number.";
                } else {
                    $questionScoresSum += (float)$questionData['score'];
                    $questionCount++;
                }

                // Validate correct answer selection
                if (!isset($questionData['correct']) || $questionData['correct'] === '') {
                    $errors[] = "Quiz $quizNumber, Question $questionNumber: Please select a correct answer.";
                }

                // Validate answer choices are not empty
                $choices = $questionData['choice'] ?? [];
                if (empty($choices)) {
                    $errors[] = "Quiz $quizNumber, Question $questionNumber: Please provide at least one answer option.";
                } else {
                    $correctIndex = (int)($questionData['correct'] ?? -1);
                    if ($correctIndex < 0 || $correctIndex >= count($choices)) {
                        $errors[] = "Quiz $quizNumber, Question $questionNumber: Invalid correct answer selection.";
                    }
                }
            }

            // Validate score distribution matches
            if ($questionCount > 0 && abs($questionScoresSum - $totalQuizScore) >= 0.01) {
                $errors[] = "Quiz $quizNumber: Score distribution mismatch! Sum of question scores ($questionScoresSum) does not match total quiz score ($totalQuizScore). Please adjust the question scores to equal the total quiz score.";
            }
        }

        return $errors;
    }

    #[Route('/{id}/edit', name: 'instructor_formation_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $qb = $entityManager->createQueryBuilder();
        $formation = $qb
            ->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.quizzes', 'q')
            ->addSelect('q')
            ->leftJoin('f.ressources', 'r')
            ->addSelect('r')
            ->where('f.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('instructor_formation_list');
        }

        // Check if the current user is the creator
        if ($formation->getCreator()?->getId() !== $user->getId()) {
            $this->addFlash('error', 'You can only modify your own formations');
            return $this->redirectToRoute('instructor_formation_list');
        }

        // Check if formation is approved before allowing modifications
        if (!$formation->isApproved()) {
            $this->addFlash('error', 'You can only modify approved formations');
            return $this->redirectToRoute('instructor_formation_list');
        }

        if ($request->isMethod('POST')) {
            $formation->setTitle($request->request->get('title'));
            $formation->setDescription($request->request->get('description'));
            $formation->setContent($request->request->get('description'));
            $formation->setPrice($request->request->get('price') ?? 0);
            $formation->setDuration($request->request->get('duration'));

            // Handle file upload
            $uploadedFile = $request->files->get('supportFile');
            if ($uploadedFile) {
                $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/formations';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                try {
                    $uploadedFile->move($uploadDir, $fileName);
                    $formation->setSupportFile('/uploads/formations/' . $fileName);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'File upload failed: ' . $e->getMessage());
                }
            }

            $entityManager->flush();

            $this->addFlash('success', "Formation \"" . $formation->getTitle() . "\" updated successfully!");
            return $this->redirectToRoute('instructor_formation_list');
        }

        return $this->render('instructor/formation/edit.html.twig', [
            'formation' => $formation
        ]);
    }

    #[Route('/{id}/archive', name: 'instructor_formation_archive', methods: ['POST'])]
    public function archive(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('instructor_formation_list');
        }

        // Check if the current user is the creator
        if ($formation->getCreator()?->getId() !== $user->getId()) {
            $this->addFlash('error', 'You can only modify your own formations');
            return $this->redirectToRoute('instructor_formation_list');
        }

        // Check if formation is approved before allowing modifications
        if (!$formation->isApproved()) {
            $this->addFlash('error', 'You can only modify approved formations');
            return $this->redirectToRoute('instructor_formation_list');
        }

        $formation->setArchived(true);
        $entityManager->flush();

        $this->addFlash('success', "Formation \"" . $formation->getTitle() . "\" archived successfully!");
        return $this->redirectToRoute('instructor_formation_list');
    }

    #[Route('/{id}/unarchive', name: 'instructor_formation_unarchive', methods: ['POST'])]
    public function unarchive(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('instructor_formation_list');
        }

        // Check if the current user is the creator
        if ($formation->getCreator()?->getId() !== $user->getId()) {
            $this->addFlash('error', 'You can only modify your own formations');
            return $this->redirectToRoute('instructor_formation_list');
        }

        // Check if formation is approved before allowing modifications
        if (!$formation->isApproved()) {
            $this->addFlash('error', 'You can only modify approved formations');
            return $this->redirectToRoute('instructor_formation_list');
        }

        $formation->setArchived(false);
        $entityManager->flush();

        $this->addFlash('success', "Formation \"" . $formation->getTitle() . "\" unarchived successfully!");
        return $this->redirectToRoute('instructor_formation_list');
    }

    #[Route('/{id}/delete', name: 'instructor_formation_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('instructor_formation_list');
        }

        // Check if the current user is the creator
        if ($formation->getCreator()?->getId() !== $user->getId()) {
            $this->addFlash('error', 'You can only delete your own formations');
            return $this->redirectToRoute('instructor_formation_list');
        }

        $formationTitle = $formation->getTitle();
        $entityManager->remove($formation);
        $entityManager->flush();

        $this->addFlash('success', "Formation \"$formationTitle\" deleted successfully!");
        return $this->redirectToRoute('instructor_formation_list');
    }
}
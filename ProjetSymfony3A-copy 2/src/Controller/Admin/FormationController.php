<?php

namespace App\Controller\Admin;

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

#[Route('/admin/formations')]
#[IsGranted('ROLE_ADMIN')]
class FormationController extends AbstractController
{
    #[Route('', name: 'admin_formation_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';
        
        $qb = $entityManager->createQueryBuilder();
        $qb->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c');

        // Search filter
        if (!empty($search)) {
            $qb->where('f.title LIKE :search OR f.description LIKE :search')
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

        return $this->render('admin/formation/list.html.twig', [
            'formations' => $formations,
            'search' => $search,
            'sort' => $sort
        ]);
    }

    #[Route('/create', name: 'admin_formation_create', methods: ['GET', 'POST'])]
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

            // If there are validation errors, display them
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->redirectToRoute('admin_formation_create');
            }

            // All validations passed, create the formation
            $formation = new Formation();
            $formation->setTitle($title);
            $formation->setDescription($description);
            $formation->setContent($description);
            $formation->setPrice((float)$price);
            $formation->setDuration((int)$duration);
            $formation->setIsApproved(true); // Admin formations are auto-approved

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
                    return $this->redirectToRoute('admin_formation_create');
                }
            }

            // Set creator as the current admin
            $user = $this->getUser();
            if ($user) {
                assert($user instanceof User);
                $formation->setCreator($user);
                $formation->addUser($user);
            }

            $entityManager->persist($formation);
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

            // Note: Admin does NOT create quizzes with formations
            // Quizzes can only be created by instructors for their own formations

            $this->addFlash('success', "Formation \"$title\" created successfully and auto-approved!");
            return $this->redirectToRoute('admin_formation_list');
        }

        return $this->render('admin/formation/add.html.twig');
    }

    #[Route('/{id}/show', name: 'admin_formation_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
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
            return $this->redirectToRoute('admin_formation_list');
        }

        return $this->render('admin/formation/show.html.twig', [
            'formation' => $formation
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_formation_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
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
            return $this->redirectToRoute('admin_formation_list');
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
            return $this->redirectToRoute('admin_formation_list');
        }

        return $this->render('admin/formation/edit.html.twig', [
            'formation' => $formation,
            'aiEnabled' => !empty($_ENV['GROQ_API_KEY']) && strpos($_ENV['GROQ_API_KEY'], 'your_key') === false
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_formation_approve', methods: ['POST'])]
    public function approve(int $id, EntityManagerInterface $entityManager): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('admin_formations_approval', ['status' => 'pending']);
        }

        $formation->setIsApproved(true);
        $entityManager->flush();

        $this->addFlash('success', "Formation \"" . $formation->getTitle() . "\" approved successfully!");
        return $this->redirectToRoute('admin_formations_approval', ['status' => 'approved']);
    }

    #[Route('/{id}/disapprove', name: 'admin_formation_disapprove', methods: ['POST'])]
    public function disapprove(int $id, EntityManagerInterface $entityManager): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('admin_formations_approval', ['status' => 'pending']);
        }

        // Archive the formation to indicate rejection
        $formation->setArchived(true);
        $entityManager->flush();

        $this->addFlash('error', "Formation \"" . $formation->getTitle() . "\" has been rejected and archived!");
        return $this->redirectToRoute('admin_formations_approval', ['status' => 'pending']);
    }

    #[Route('/{id}/delete', name: 'admin_formation_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('admin_formation_list');
        }

        $formationTitle = $formation->getTitle();
        $entityManager->remove($formation);
        $entityManager->flush();

        $this->addFlash('success', "Formation \"$formationTitle\" deleted successfully!");
        return $this->redirectToRoute('admin_formation_list');
    }

    #[Route('/{id}/archive', name: 'admin_formation_archive', methods: ['POST'])]
    public function archive(int $id, EntityManagerInterface $entityManager): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('admin_formation_list');
        }

        $formation->setArchived(true);
        $entityManager->flush();

        $this->addFlash('success', "Formation \"" . $formation->getTitle() . "\" archived successfully!");
        return $this->redirectToRoute('admin_formation_list');
    }

    #[Route('/{id}/unarchive', name: 'admin_formation_unarchive', methods: ['POST'])]
    public function unarchive(int $id, EntityManagerInterface $entityManager): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('admin_formation_list');
        }

        $formation->setArchived(false);
        $entityManager->flush();

        $this->addFlash('success', "Formation \"" . $formation->getTitle() . "\" unarchived successfully!");
        return $this->redirectToRoute('admin_formation_list');
    }

    /**
     * Validate quiz structure and score distributions
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
}

<?php

namespace App\Controller\Instructor;

use App\Entity\Cours;
use App\Entity\Chapitre;
use App\Entity\User;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor/course', name: 'instructor_course_')]
#[IsGranted('ROLE_INSTRUCTOR')]
class CoursController extends AbstractController
{
    public function __construct(
        private CoursRepository $coursRepository,
        private ChapitreRepository $chapitreRepository,
        private EntityManagerInterface $em
    ) {}

    /**
     * List own courses (all statuses) + other approved courses (read-only)
     */
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        // Build query for own courses
        $qb = $this->coursRepository->createQueryBuilder('c')
            ->leftJoin('c.creator', 'creator')
            ->addSelect('creator')
            ->where('c.creator = :creator')
            ->setParameter('creator', $user);

        // Apply search filter
        if ($search) {
            $qb->andWhere('c.title LIKE :search OR c.description LIKE :search')
               ->setParameter('search', "%$search%");
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('c.createdAt', 'ASC');
                break;
            case 'a_z':
                $qb->orderBy('c.title', 'ASC');
                break;
            case 'z_a':
                $qb->orderBy('c.title', 'DESC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('c.createdAt', 'DESC');
                break;
        }

        $ownCourses = $qb->getQuery()->getResult();
        
        // Approved courses from other instructors (read-only) - not filtered by search/sort
        $approvedOtherCourses = $this->coursRepository->findApprovedExcludingCreator($user);

        return $this->render('instructor/course/list.html.twig', [
            'ownCourses' => $ownCourses,
            'approvedOtherCourses' => $approvedOtherCourses,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    /**
     * Create new course (instructor creates with auto-pending status)
     */
    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $cours = new Cours();
        // Instructor-created courses are pending until admin approves
        $cours->setStatus(Cours::STATUS_PENDING);
        $cours->setCreator($user);

        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file uploads
            $this->handleFileUploads($cours);

            // Set creator for each chapter
            foreach ($cours->getChapitres() as $chapitre) {
                $chapitre->setCreator($user);
            }

            $this->em->persist($cours);
            $this->em->flush();

            $this->addFlash('success', 'Course created successfully and sent for approval!');
            return $this->redirectToRoute('instructor_course_list');
        }

        return $this->render('instructor/course/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * View own course details with all chapters
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Cours $cours): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        // Check if user is owner or course is approved
        if ($cours->getCreator() !== $user && !$cours->isApproved()) {
            throw $this->createAccessDeniedException('You do not have access to this course.');
        }

        $chapters = $this->chapitreRepository->findByCours($cours);
        $isOwner = $cours->getCreator() === $user;

        return $this->render('instructor/course/show.html.twig', [
            'cours' => $cours,
            'chapters' => $chapters,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Edit own course only
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cours): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        // Only allow editing own courses
        if ($cours->getCreator() !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own courses.');
        }

        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file uploads
            $this->handleFileUploads($cours);

            // Set creator for new chapters
            foreach ($cours->getChapitres() as $chapitre) {
                if (!$chapitre->getId()) {
                    $chapitre->setCreator($user);
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Course updated successfully!');
            return $this->redirectToRoute('instructor_course_show', ['id' => $cours->getId()]);
        }

        return $this->render('instructor/course/edit.html.twig', [
            'form' => $form->createView(),
            'cours' => $cours,
        ]);
    }

    /**
     * Delete own course only
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cours): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        // Only allow deleting own courses
        if ($cours->getCreator() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own courses.');
        }

        if ($this->isCsrfTokenValid('delete' . $cours->getId(), $request->request->get('_token'))) {
            $this->em->remove($cours);
            $this->em->flush();
            $this->addFlash('success', 'Course deleted successfully!');
        }

        return $this->redirectToRoute('instructor_course_list');
    }

    /**
     * Handle file uploads for course materials
     */
    private function handleFileUploads(Cours $cours): void
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/courses';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle course image upload
        if ($cours->getCourseImage() !== null) {
            $file = $cours->getCourseImage();
            if (is_object($file)) {
                $filename = 'image_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $cours->setCourseImage('uploads/courses/' . $filename);
            }
        }

        // Handle course PDF upload
        if ($cours->getCourseFile() !== null) {
            $file = $cours->getCourseFile();
            if (is_object($file)) {
                $filename = 'file_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $cours->setCourseFile('uploads/courses/' . $filename);
            }
        }

        // Handle course video upload
        if ($cours->getCourseVideo() !== null) {
            $file = $cours->getCourseVideo();
            if (is_object($file)) {
                $filename = 'video_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $cours->setCourseVideo('uploads/courses/' . $filename);
            }
        }
    }
}

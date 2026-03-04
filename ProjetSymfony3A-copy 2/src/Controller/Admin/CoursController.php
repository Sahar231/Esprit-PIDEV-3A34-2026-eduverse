<?php

namespace App\Controller\Admin;

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

#[Route('/admin/course', name: 'admin_course_')]
#[IsGranted('ROLE_ADMIN')]
class CoursController extends AbstractController
{
    public function __construct(
        private CoursRepository $coursRepository,
        private ChapitreRepository $chapitreRepository,
        private EntityManagerInterface $em
    ) {}

    /**
     * List all courses with all statuses
     */
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        $qb = $this->coursRepository->createQueryBuilder('c')
            ->leftJoin('c.creator', 'creator')
            ->addSelect('creator');

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

        $courses = $qb->getQuery()->getResult();
        $pendingCount = $this->coursRepository->countPending();
        $approvedCount = $this->coursRepository->countApproved();
        $refusedCount = $this->coursRepository->countRefused();

        return $this->render('admin/course/list.html.twig', [
            'courses' => $courses,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'refusedCount' => $refusedCount,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    /**
     * Create new course (admin creates with auto-approved status)
     */
    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $cours = new Cours();
        // Admin-created courses are auto-approved
        $cours->setStatus(Cours::STATUS_APPROVED);
        $user = $this->getUser();
        assert($user instanceof User);
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

            $this->addFlash('success', 'Course created successfully!');
            return $this->redirectToRoute('admin_course_list');
        }

        return $this->render('admin/course/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * View course details with all chapters
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Cours $cours): Response
    {
        $chapters = $this->chapitreRepository->findByCours($cours);

        return $this->render('admin/course/show.html.twig', [
            'cours' => $cours,
            'chapters' => $chapters,
        ]);
    }

    /**
     * Edit course
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cours): Response
    {
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file uploads
            $this->handleFileUploads($cours);

            // Set creator for new chapters (use current course creator)
            $creator = $cours->getCreator();
            foreach ($cours->getChapitres() as $chapitre) {
                if (!$chapitre->getId()) {
                    $chapitre->setCreator($creator);
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Course updated successfully!');
            return $this->redirectToRoute('admin_course_show', ['id' => $cours->getId()]);
        }

        return $this->render('admin/course/edit.html.twig', [
            'form' => $form->createView(),
            'cours' => $cours,
        ]);
    }

    /**
     * Delete course
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cours): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cours->getId(), $request->request->get('_token'))) {
            $this->em->remove($cours);
            $this->em->flush();
            $this->addFlash('success', 'Course deleted successfully!');
        }

        return $this->redirectToRoute('admin_course_list');
    }

    /**
     * Approve pending course (POST action for AJAX/form submission)
     */
    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Request $request, Cours $cours): Response
    {
        if ($this->isCsrfTokenValid('approve' . $cours->getId(), $request->request->get('_token'))) {
            $cours->approve();
            $this->em->flush();
            $this->addFlash('success', 'Course approved successfully!');
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $cours->getId()]);
    }

    /**
     * Refuse/reject pending course (POST action for AJAX/form submission)
     */
    #[Route('/{id}/refuse', name: 'refuse', methods: ['POST'])]
    public function refuse(Request $request, Cours $cours): Response
    {
        if ($this->isCsrfTokenValid('refuse' . $cours->getId(), $request->request->get('_token'))) {
            $cours->refuse();
            $this->em->flush();
            $this->addFlash('success', 'Course refused successfully!');
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $cours->getId()]);
    }

    /**
     * Approval management page for pending courses
     */
    #[Route('/approvals/all', name: 'approvals', methods: ['GET'])]
    public function approvals(Request $request): Response
    {
        $status = $request->query->get('status', 'pending');
        
        $courses = match($status) {
            'approved' => $this->coursRepository->findApproved(),
            'refused' => $this->coursRepository->findRefused(),
            default => $this->coursRepository->findPending(),
        };

        $pendingCount = $this->coursRepository->countPending();
        $approvedCount = $this->coursRepository->countApproved();
        $refusedCount = $this->coursRepository->countRefused();

        return $this->render('admin/course/approvals.html.twig', [
            'courses' => $courses,
            'currentStatus' => $status,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'refusedCount' => $refusedCount,
        ]);
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


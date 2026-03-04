<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\Club;
use App\Entity\Formation;
use App\Entity\Evaluation;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use App\Repository\ClubRepository;
use App\Repository\FormationRepository;
use App\Repository\EvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Service\Paginator;
use App\Service\PdfService;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private Paginator $paginator,
        private PdfService $pdfService
    ) {}

    #[Route('/students', name: 'students')]
    public function students(Request $request, UserRepository $userRepo): Response
    {
        return $this->renderUserList($request, $userRepo, 'ROLE_STUDENT', 'admin/user/management.html.twig');
    }

    #[Route('/instructors', name: 'instructors')]
    public function instructors(Request $request, UserRepository $userRepo): Response
    {
        return $this->renderUserList($request, $userRepo, 'ROLE_INSTRUCTOR', 'admin/user/management.html.twig');
    }

    #[Route('/instructor-requests', name: 'instructor_requests')]
    public function instructorRequests(Request $request, UserRepository $userRepo): Response
    {
        $request->query->set('status', 'pending');
        return $this->renderUserList($request, $userRepo, 'ROLE_INSTRUCTOR', 'admin/user/management.html.twig');
    }

    #[Route('/student-requests', name: 'student_requests')]
    public function studentRequests(Request $request, UserRepository $userRepo): Response
    {
        $request->query->set('status', 'pending');
        return $this->renderUserList($request, $userRepo, 'ROLE_STUDENT', 'admin/user/management.html.twig');
    }

    private function renderUserList(Request $request, UserRepository $userRepo, string $role, string $template): Response
    {
        $search = $request->query->get('q', '');
        $status = $request->query->get('status', ''); // pending, approved, rejected, blocked
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'DESC');
        $page = (int) $request->query->get('page', 1);

        $allowedSorts = ['id', 'fullName', 'email', 'createdAt', 'isBlocked'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'id';

        $qb = $userRepo->findUsersByRoleAndStatus($role, $status, $search, $sort, $direction);
        $pagination = $this->paginator->paginate($qb, $page, 10);

        return $this->render($template, [
            'pagination' => $pagination,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
            'role' => $role,
            'role_label' => $role === 'ROLE_INSTRUCTOR' ? 'Instructor' : 'Student'
        ]);
    }

    #[Route('/approvals/{type}', name: 'approvals')]
    public function approvals(string $type, EntityManagerInterface $em): Response
    {
        $items = match($type) {
            'courses' => $em->getRepository(Course::class)->findBy(['isApproved' => false]),
            'clubs' => $em->getRepository(Club::class)->findBy(['status' => 'PENDING']),
            'formations' => $em->getRepository(Formation::class)->findBy(['isApproved' => false]),
            'evaluations' => $em->getRepository(Evaluation::class)->findBy(['isApproved' => false]),
            default => []
        };

        $itemCount = count($items);
        $itemsPerPage = 10;
        $pages = max(1, ceil($itemCount / $itemsPerPage));

        return $this->render('admin/approvals-list.html.twig', [
            'items' => $items,
            'type' => $type,
            'pages' => $pages,
            'current_page' => 1,
            'total_items' => $itemCount
        ]);
    }

    #[Route('/formation/{id}/approve-detail', name: 'formation_approve_detail')]
    public function formationApproveDetail(Formation $formation): Response
    {
        return $this->render('admin/formation-approval-detail.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/user/{id}/toggle-block', name: 'user_toggle_block')]
    public function toggleBlock(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsBlocked(!$user->isBlocked());
        $em->flush();
        $this->addFlash('success', 'User status updated successfully.');
        return $this->redirect($this->generateUrl('admin_' . (in_array('ROLE_STUDENT', $user->getRoles()) ? 'students' : 'instructors')));
    }

    #[Route('/user/{id}/approve', name: 'user_approve')]
    public function approve(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsApproved(true);
        $em->flush();
        $this->addFlash('success', 'User approved.');
        return $this->redirectToRoute(in_array('ROLE_STUDENT', $user->getRoles()) ? 'admin_student_requests' : 'admin_instructor_requests');
    }

    #[Route('/user/{id}/reject', name: 'user_reject')]
    public function reject(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsRejected(true);
        $user->setIsApproved(false); // Ensure it's not approved
        $em->flush();
        $this->addFlash('success', 'User request rejected.');
        
        $role = in_array('ROLE_STUDENT', $user->getRoles()) ? 'student' : 'instructor';
        return $this->redirectToRoute('admin_' . $role . '_requests');
    }

    #[Route('/approve-item/{type}/{id}', name: 'item_approve')]
    public function approveItem(string $type, int $id, EntityManagerInterface $em): Response
    {
        $item = match($type) {
            'courses' => $em->getRepository(Course::class)->find($id),
            'clubs' => $em->getRepository(Club::class)->find($id),
            'formations' => $em->getRepository(Formation::class)->find($id),
            'evaluations' => $em->getRepository(Evaluation::class)->find($id),
            default => null
        };

        if ($item) {
            if ($type === 'clubs') {
                if (method_exists($item, 'setStatus')) {
                    $item->setStatus('APPROVED');
                }
            } else {
                if (method_exists($item, 'setIsApproved')) {
                    $item->setIsApproved(true);
                }
            }
            $em->flush();
            $this->addFlash('success', 'Item approved.');
        }

        return $this->redirectToRoute('admin_approvals', ['type' => $type]);
    }

    #[Route('/reject-item/{type}/{id}', name: 'item_reject')]
    public function rejectItem(string $type, int $id, EntityManagerInterface $em): Response
    {
        $item = match($type) {
            'courses' => $em->getRepository(Course::class)->find($id),
            'clubs' => $em->getRepository(Club::class)->find($id),
            'formations' => $em->getRepository(Formation::class)->find($id),
            'evaluations' => $em->getRepository(Evaluation::class)->find($id),
            default => null
        };

        if ($item) {
            $em->remove($item);
            $em->flush();
            $this->addFlash('success', 'Item rejected.');
        }

        return $this->redirectToRoute('admin_approvals', ['type' => $type]);
    }

    #[Route('/user/add/{role}', name: 'user_add')]
    public function addUser(string $role, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $user->setRole(ucfirst($role));
        $form = $this->createForm(\App\Form\UserFormType::class, $user, ['is_creation' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $user->setIsApproved(true);
            $user->setRoles([$role === 'instructor' ? 'ROLE_INSTRUCTOR' : 'ROLE_STUDENT']);
            $user->setCreatedAt(new \DateTimeImmutable());
            
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute($role === 'instructor' ? 'admin_instructors' : 'admin_students');
        }

        return $this->render('admin/user-form.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
            'role_label' => $role === 'instructor' ? 'Instructor' : 'Student',
            'action' => 'Add'
        ]);
    }

    #[Route('/user/edit/{id}', name: 'user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(\App\Form\UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleInput = $user->getRole();
            $user->setRoles([$roleInput === 'Instructor' ? 'ROLE_INSTRUCTOR' : 'ROLE_STUDENT']);
            $em->flush();

            return $this->redirectToRoute($user->getRole() === 'Instructor' ? 'admin_instructors' : 'admin_students');
        }

        $role = strtolower((string)$user->getRole());

        return $this->render('admin/user-form.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
            'role_label' => $role === 'instructor' ? 'Instructor' : 'Student',
            'action' => 'Edit'
        ]);
    }

    #[Route('/user/delete/{id}', name: 'user_delete')]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $role = strtolower($user->getRole());
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute($role === 'instructor' ? 'admin_instructors' : 'admin_students');
    }

    #[Route('/courses', name: 'courses')]
    public function courses(): Response
    {
        return $this->render('admin/admin-course-list.html.twig');
    }

    #[Route('/course-category', name: 'course_category')]
    public function courseCategory(): Response
    {
        return $this->render('admin/admin-course-category.html.twig');
    }

    #[Route('/reviews', name: 'reviews')]
    public function reviews(): Response
    {
        return $this->render('admin/admin-review.html.twig');
    }

    #[Route('/earnings', name: 'earnings')]
    public function earnings(): Response
    {
        return $this->render('admin/admin-earning.html.twig');
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('admin/admin-setting.html.twig');
    }
}

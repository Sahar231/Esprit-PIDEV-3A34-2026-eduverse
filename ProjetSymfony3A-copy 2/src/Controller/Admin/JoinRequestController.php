<?php

namespace App\Controller\Admin;

use App\Entity\JoinRequest;
use App\Repository\JoinRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/join-request', name: 'admin_join_request_')]
#[IsGranted('ROLE_ADMIN')]
class JoinRequestController extends AbstractController
{
    public function __construct(
        private JoinRequestRepository $joinRequestRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $status = $request->query->get('status', 'PENDING');
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        $qb = $this->joinRequestRepository->createQueryBuilder('jr')
            ->leftJoin('jr.club', 'club')
            ->addSelect('club')
            ->leftJoin('jr.user', 'user')
            ->addSelect('user');

        // Filter by status
        if ($status) {
            $qb->andWhere('jr.status = :status')
               ->setParameter('status', $status);
        }

        // Apply search filter
        if ($search) {
            $qb->andWhere('club.name LIKE :search OR user.fullName LIKE :search')
               ->setParameter('search', "%$search%");
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('jr.requestedAt', 'ASC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('jr.requestedAt', 'DESC');
                break;
        }

        $joinRequests = $qb->getQuery()->getResult();
        
        // Calculate statistics
        $totalRequests = $this->joinRequestRepository->count([]);
        $pendingRequests = $this->joinRequestRepository->count(['status' => JoinRequest::STATUS_PENDING]);
        $approvedRequests = $this->joinRequestRepository->count(['status' => JoinRequest::STATUS_APPROVED]);

        return $this->render('admin/join_request/list.html.twig', [
            'joinRequests' => $joinRequests,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
        ]);
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Request $request, JoinRequest $joinRequest): Response
    {
        if ($this->isCsrfTokenValid('approve' . $joinRequest->getId(), $request->request->get('_token'))) {
            $joinRequest->approve();
            
            // Add user to club members
            $joinRequest->getClub()->addMember($joinRequest->getUser());
            
            $this->em->flush();

            // Send notification email
            $email = (new TemplatedEmail())
                ->from(new Address('gomriyoussef2004@gmail.com', 'Eduverse'))
                ->to((string) $joinRequest->getUser()->getEmail())
                ->subject('Adhésion au club approuvée')
                ->htmlTemplate('emails/join_request_approved.html.twig')
                ->context([
                    'user' => $joinRequest->getUser(),
                    'club' => $joinRequest->getClub(),
                ]);
            
            $this->mailer->send($email);

            $this->addFlash('success', 'Demande d\'adhésion approuvée et e-mail envoyé !');
        }

        return $this->redirectToRoute('admin_join_request_list');
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(Request $request, JoinRequest $joinRequest): Response
    {
        if ($this->isCsrfTokenValid('reject' . $joinRequest->getId(), $request->request->get('_token'))) {
            $joinRequest->reject();
            $this->em->flush();

            // Send notification email
            $email = (new TemplatedEmail())
                ->from(new Address('gomriyoussef2004@gmail.com', 'Eduverse'))
                ->to((string) $joinRequest->getUser()->getEmail())
                ->subject('Information sur votre demande d\'adhésion')
                ->htmlTemplate('emails/join_request_rejected.html.twig')
                ->context([
                    'user' => $joinRequest->getUser(),
                    'club' => $joinRequest->getClub(),
                ]);
            
            $this->mailer->send($email);

            $this->addFlash('success', 'Demande d\'adhésion refusée et e-mail envoyé !');
        }

        return $this->redirectToRoute('admin_join_request_list');
    }
}

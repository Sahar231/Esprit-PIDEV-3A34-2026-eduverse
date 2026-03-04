<?php

namespace App\Controller\Student;

use App\Entity\Wallet;
use App\Entity\User;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/wallet')]
#[IsGranted('ROLE_STUDENT')]
class WalletController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
    ) {
    }

    #[Route('', name: 'student_wallet_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        /** @var \App\Repository\WalletRepository $repo */
        $repo = $entityManager->getRepository(Wallet::class);
        $wallet = $repo->findByUser($user);

        return $this->render('student/wallet/index.html.twig', [
            'wallet' => $wallet,
        ]);
    }

    #[Route('/create', name: 'student_wallet_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        
        // Check if user already has a wallet
        /** @var \App\Repository\WalletRepository $repo */
        $repo = $entityManager->getRepository(Wallet::class);
        $existingWallet = $repo->findByUser($user);
        if ($existingWallet) {
            $this->addFlash('warning', 'You already have a wallet');
            return $this->redirectToRoute('student_wallet_index');
        }

        if ($request->isMethod('POST')) {

            // Create new wallet with 100 credits for first-time creation
            $wallet = new Wallet();
            $wallet->setUser($user);
            $wallet->setBalance(100); // First-time bonus of 100 credits

            $entityManager->persist($wallet);
            $entityManager->flush();

            $this->addFlash('success', 'Wallet created successfully! You have received 100 free credits.');
            return $this->redirectToRoute('student_wallet_index');
        }

        return $this->render('student/wallet/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'student_wallet_edit', methods: ['GET', 'POST'])]
    public function edit(Wallet $wallet, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Ensure the wallet belongs to the current user
        if ($wallet->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You can only edit your own wallet');
            return $this->redirectToRoute('student_wallet_index');
        }

        if ($request->isMethod('POST')) {
            $entityManager->flush();

            $this->addFlash('success', 'Wallet information updated successfully!');
            return $this->redirectToRoute('student_wallet_index');
        }

        return $this->render('student/wallet/edit.html.twig', [
            'wallet' => $wallet,
        ]);
    }

    #[Route('/{id}/delete', name: 'student_wallet_delete', methods: ['POST'])]
    public function delete(Wallet $wallet, EntityManagerInterface $entityManager): Response
    {
        // Ensure the wallet belongs to the current user
        if ($wallet->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You can only delete your own wallet');
            return $this->redirectToRoute('student_wallet_index');
        }

        $entityManager->remove($wallet);
        $entityManager->flush();

        $this->addFlash('success', 'Wallet deleted successfully!');
        return $this->redirectToRoute('student_wallet_index');
    }

    /**
     * Create a Stripe checkout session for purchasing credits
     *
     * POST to /student/wallet/checkout with credits parameter
     * Example: POST /student/wallet/checkout?credits=10
     */
    #[Route('/checkout', name: 'student_wallet_checkout', methods: ['POST'])]
    public function createCheckout(Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $credits = (int)$request->request->get('credits', 0);

        // Validate credits amount
        if ($credits < 1 || $credits > 1000) {
            $this->addFlash('error', 'Please select a valid credit amount (1-1000)');
            return $this->redirectToRoute('student_wallet_index');
        }

        try {
            // Create Stripe checkout session
            $result = $this->stripeService->createCheckoutSession(
                user: $user,
                credits: $credits,
                successUrl: $this->generateUrl('student_wallet_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                cancelUrl: $this->generateUrl('student_wallet_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            );

            // Redirect to Stripe Checkout
            return $this->redirect($result['session']->url);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while processing your payment: ' . $e->getMessage());
            return $this->redirectToRoute('student_wallet_index');
        }
    }

    /**
     * Success page after payment (don't update wallet here - wait for webhook!)
     *
     * GET /student/wallet/success?session_id=...
     */
    #[Route('/success', name: 'student_wallet_success', methods: ['GET'])]
    public function paymentSuccess(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Invalid session ID');
            return $this->redirectToRoute('student_wallet_index');
        }

        try {
            // Retrieve the session to verify payment
            $session = $this->stripeService->getCheckoutSession($sessionId);

            // The wallet will be updated by the webhook when Stripe confirms payment
            // Here we just show a success message
            $this->addFlash('success', 'Payment processing! Your credits will be added shortly.');

            return $this->render('student/wallet/success.html.twig', [
                'session' => $session,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Could not verify payment: ' . $e->getMessage());
            return $this->redirectToRoute('student_wallet_index');
        }
    }

    /**
     * Cancel page if user cancels payment
     *
     * GET /student/wallet/cancel
     */
    #[Route('/cancel', name: 'student_wallet_cancel', methods: ['GET'])]
    public function paymentCancelled(): Response
    {
        $this->addFlash('warning', 'Payment was cancelled. You were not charged.');
        return $this->redirectToRoute('student_wallet_index');
    }
}
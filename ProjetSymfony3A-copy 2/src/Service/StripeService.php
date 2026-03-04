<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\WalletTransaction;
use App\Repository\WalletTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\WebhookEndpoint;

class StripeService
{
    private string $stripeSecretKey;
    private string $stripeWebhookSecret;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private WalletTransactionRepository $transactionRepository,
        string $stripeSecretKey,
        string $stripeWebhookSecret,
    ) {
        $this->stripeSecretKey = $stripeSecretKey;
        $this->stripeWebhookSecret = $stripeWebhookSecret;
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Create a Stripe Checkout session for purchasing credits
     *
     * @param User $user The user purchasing credits
     * @param int $credits Number of credits to purchase
     * @param string $successUrl URL to redirect after successful payment
     * @param string $cancelUrl URL to redirect if payment is cancelled
     *
     * @return array ['session' => Session, 'transaction' => WalletTransaction]
     */
    public function createCheckoutSession(
        User $user,
        int $credits,
        string $successUrl,
        string $cancelUrl
    ): array {
        // Create the Stripe checkout session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $user->getEmail(),
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Wallet Credits',
                            'description' => "$credits credits for your EduVerse wallet",
                        ],
                        'unit_amount' => $credits * 100, // Stripe uses cents, 1 euro = 100 cents
                    ],
                    'quantity' => 1,
                ]
            ],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'user_id' => (string) $user->getId(),
                'credits' => (string) $credits,
            ],
        ]);

        // Create a pending wallet transaction
        $transaction = new WalletTransaction();
        $transaction->setUser($user);
        $transaction->setCredits($credits);
        $transaction->setAmount((string)($credits)); // 1 credit = 1 euro
        $transaction->setStatus('pending');
        $transaction->setStripeSessionId($session->id);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return [
            'session' => $session,
            'transaction' => $transaction,
        ];
    }

    /**
     * Retrieve a Stripe checkout session
     */
    public function getCheckoutSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }

    /**
     * Handle the Stripe webhook event (checkout.session.completed)
     * This is called when payment is successfully completed
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Stripe signature header
     *
     * @throws SignatureVerificationException if signature is invalid
     */
    public function handleWebhookEvent(string $payload, string $signature): void
    {
        // Verify the signature to ensure the request comes from Stripe
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            $this->stripeWebhookSecret
        );

        // Handle the event
        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutSessionCompleted($event->data->object);
        }
    }

    /**
     * Process a completed checkout session
     * This updates the wallet balance and marks the transaction as paid
     */
    private function handleCheckoutSessionCompleted(Session $session): void
    {
        // Find the transaction by Stripe session ID
        $transaction = $this->transactionRepository->findByStripeSessionId($session->id);

        if (!$transaction) {
            // Log warning: transaction not found
            return;
        }

        // Only process if transaction is still pending
        if (!$transaction->isPending()) {
            // Already processed or failed
            return;
        }

        // Get the user and their wallet
        $user = $transaction->getUser();
        $wallet = $user->getWallet();

        if (!$wallet) {
            // Create wallet if it doesn't exist (shouldn't happen normally)
            $wallet = new \App\Entity\Wallet();
            $wallet->setUser($user);
            $this->entityManager->persist($wallet);
        }

        // Update the wallet balance
        $wallet->addBalance($transaction->getCredits());

        // Mark the transaction as paid
        $transaction->setStatus('paid');
        $transaction->setCompletedAt(new \DateTimeImmutable());

        // Save changes
        $this->entityManager->flush();
    }

    /**
     * Check if an API key is valid by attempting a simple API call
     */
    public function isApiKeyValid(string $apiKey): bool
    {
        try {
            Stripe::setApiKey($apiKey);
            \Stripe\Account::retrieve();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeWebhookController extends AbstractController
{
    public function __construct(private StripeService $stripeService)
    {
    }

    /**
     * Stripe webhook endpoint
     * Configure this in Stripe Dashboard at: https://dashboard.stripe.com/webhooks
     *
     * Route: POST /stripe/webhook
     * Subscribe to: checkout.session.completed
     */
    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): Response
    {
        // Get the raw request body (IMPORTANT: must be raw, not decoded JSON)
        $payload = $request->getContent();
        
        // Get the signature from headers
        $signature = $request->headers->get('stripe-signature');

        if (!$signature) {
            return new Response('Missing Stripe signature', Response::HTTP_BAD_REQUEST);
        }

        try {
            // Process the webhook event
            // This will verify the signature and handle the payment
            $this->stripeService->handleWebhookEvent($payload, $signature);

            return new Response('Webhook processed successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            // Log the error in production
            error_log('Stripe webhook error: ' . $e->getMessage());
            
            // Return 400 to tell Stripe to retry later
            return new Response('Webhook error: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}

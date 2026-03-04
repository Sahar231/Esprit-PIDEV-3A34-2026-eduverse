<?php

namespace App\Controller\Api;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/chatbot', name: 'api_chatbot_')]
#[IsGranted('ROLE_USER')]
class ChatbotController extends AbstractController
{
    public function __construct(
        private ChatbotService $chatbotService,
    ) {
    }

    /**
     * Handle chatbot messages
     */
    #[Route('/ask', name: 'ask', methods: ['POST'])]
    public function ask(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['question']) || empty($data['question'])) {
                return new JsonResponse(
                    ['success' => false, 'error' => 'Question is required'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $question = trim($data['question']);
            $context = isset($data['context']) ? trim($data['context']) : '';

            // Validate question length
            if (strlen($question) > 1000) {
                return new JsonResponse(
                    ['success' => false, 'error' => 'Question is too long (max 1000 characters)'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Get answer from chatbot
            $answer = $this->chatbotService->getAnswer($question, $context);

            return new JsonResponse([
                'success' => true,
                'answer' => $answer,
            ]);
        } catch (\Exception $e) {
            error_log('Chatbot API error: ' . $e->getMessage() . ' (' . get_class($e) . ')');
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            return new JsonResponse(
                [
                    'success' => false,
                    'error' => 'ChatBot service error: ' . $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

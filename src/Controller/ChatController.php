<?php

namespace App\Controller;

use App\Service\GroqChatService;
use App\Service\LocalChatService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class ChatController extends AbstractController
{
    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(Request $request, GroqChatService $groq, LocalChatService $localChat, LoggerInterface $logger): JsonResponse
    {
        $logger->info('=== Chat API called ===');
        
        try {
            $content = $request->getContent();
            $logger->info('Raw content length: ' . strlen($content));
            
            if (empty($content)) {
                $logger->error('Request content is empty');
                return $this->json(['error' => 'Empty request body'], 400);
            }
            
            $body = json_decode($content, true);
            if (!is_array($body)) {
                $logger->error('Failed to decode JSON');
                return $this->json(['error' => 'Invalid JSON format'], 400);
            }

            $message = trim($body['message'] ?? '');
            $logger->info('Message received: ' . substr($message, 0, 50));

            if (empty($message)) {
                return $this->json(['error' => 'Message cannot be empty'], 400);
            }

            // Try Groq API first (free and dynamic)
            try {
                $logger->info('Attempting Groq API service...');
                $response = $groq->chat($message);
                
                // Log the full response for debugging
                $logger->info('Groq response keys: ' . implode(', ', array_keys($response)));
                
                // Vérifier s'il y a une erreur
                if (isset($response['error']) && !empty($response['error'])) {
                    throw new \Exception('Groq Error: ' . ($response['error']['message'] ?? 'Unknown error'));
                }
                
                // Extraire le texte
                $answer = $groq->extractText($response);
                if (!empty($answer)) {
                    $logger->info('Groq response successful');
                    return $this->json([
                        'answer' => $answer,
                        'success' => true,
                        'source' => 'groq'
                    ]);
                }
            } catch (\Throwable $e) {
                $logger->warning('Groq failed, falling back to local service: ' . $e->getMessage());
            }

            // Fallback to local chatbot
            $logger->info('Using local chat service as fallback');
            $answer = $localChat->chat($message);
            
            return $this->json([
                'answer' => $answer,
                'success' => true,
                'source' => 'local'
            ]);
            
        } catch (\Throwable $e) {
            $logger->error('Exception caught: ' . $e->getMessage());
            $logger->error('Stack trace: ' . $e->getTraceAsString());
            
            return $this->json([
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}
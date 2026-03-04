<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    #[Route('/api/generate-description', name: 'api_generate_description', methods: ['POST'])]
    public function generateDescription(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Support multiple field names for flexibility
            $name = $data['name'] ?? $data['title'] ?? $data['clubName'] ?? null;
            $entityType = $data['entityType'] ?? $data['type'] ?? 'club';

            if (!$name) {
                return new JsonResponse(['error' => 'Nom requis. Veuillez remplir le champ de nom.'], 400);
            }

            $apiKey = $_ENV['GROQ_API_KEY'] ?? null;

            if (!$apiKey || strpos($apiKey, 'your_key') !== false || $apiKey === 'gsk_') {
                return new JsonResponse(['error' => 'Clé API Groq non configurée. Veuillez configurer GROQ_API_KEY dans .env'], 500);
            }

            // Generate prompts based on entity type
            $prompts = [
                'formation' => "Génère une description courte et professionnelle en français pour une formation qui s'appelle: " . $name,
                'club' => "Génère une description courte et professionnelle en français pour un club universitaire qui s'appelle: " . $name,
                'course' => "Génère une description courte et professionnelle en français pour un cours qui s'appelle: " . $name,
                'event' => "Génère une description courte et professionnelle en français pour un événement qui s'appelle: " . $name,
            ];

            $prompt = $prompts[$entityType] ?? $prompts['club'];

            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 300,
                    'temperature' => 0.7,
                ],
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() !== 200) {
                $errorContent = $response->getContent(false);
                return new JsonResponse(['error' => 'API Groq a retourné une erreur: ' . substr($errorContent, 0, 200)], $response->getStatusCode());
            }

            $result = $response->toArray();
            
            if (!isset($result['choices'][0]['message']['content'])) {
                return new JsonResponse(['error' => 'Format de réponse invalide depuis l\'API'], 500);
            }
            
            $description = $result['choices'][0]['message']['content'] ?? '';

            return new JsonResponse(['description' => trim($description)]);
        } catch (\Exception $e) {
            error_log('Generate description error: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $clubName = $data['clubName'] ?? 'ce club';
        $description = $data['description'] ?? 'Pas de description';
        $events = $data['events'] ?? [];
        $userMessage = $data['message'] ?? null;

        if (!$userMessage) {
            return new JsonResponse(['error' => 'Message is required.'], 400);
        }

        $apiKey = $_ENV['GROQ_API_KEY'] ?? null;
        if (!$apiKey || strpos($apiKey, 'your_key') !== false) {
            return new JsonResponse(['error' => 'Groq API key not configured.'], 500);
        }

        $eventsList = empty($events) ? 'Aucun événement à venir.' : implode(', ', array_map(fn($e) => $e['title'] . ' (' . $e['date'] . ')', $events));
        
        $systemPrompt = "Tu es un assistant pour le club $clubName. Voici les informations du club: $description. Les événements: $eventsList. Réponds aux questions des utilisateurs en français de manière polie et concise.";

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage]
                    ],
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return new JsonResponse(['error' => 'Groq API returned error.'], $response->getStatusCode());
            }

            $result = $response->toArray();
            $reply = $result['choices'][0]['message']['content'] ?? '';

            return new JsonResponse(['reply' => trim($reply)]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to reach AI.'], 500);
        }
    }
}


<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GroqChatService
{
    private string $apiKey;
    private string $model = 'mixtral-8x7b-32768'; // Free Groq model
    private int $timeout = 30;

    public function __construct(
        private HttpClientInterface $httpClient,
        string $groqApiKey
    ) {
        $this->apiKey = $groqApiKey;
    }

    public function chat(string $message): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Groq API key not configured');
        }

        try {
            $payload = [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu es un assistant pédagogique expert pour aider les étudiants à comprendre des sujets informatiques. Réponds de manière claire, concise et pédagogique en français. Tes réponses doivent être courtes (2-3 phrases max).'
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 200,
                'top_p' => 1
            ];

            $response = $this->httpClient->request(
                'POST',
                'https://api.groq.com/openai/v1/chat/completions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                    'timeout' => $this->timeout,
                ]
            );

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            if ($statusCode !== 200) {
                return [
                    'error' => [
                        'message' => $content['error']['message'] ?? 'Groq API returned error'
                    ]
                ];
            }

            return $content;

        } catch (\Exception $e) {
            return [
                'error' => [
                    'message' => 'Groq request failed: ' . $e->getMessage()
                ]
            ];
        }
    }

    public function extractText(array $response): string
    {
        if (isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }

        return '';
    }
}

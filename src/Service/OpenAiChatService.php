<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenAiChatService
{
    public function __construct(
        private HttpClientInterface $http,
        private string $apiKey,
        private string $model,
    ) {}

    public function chat(string $message, ?string $previousResponseId = null): array
    {
        // Vérifier la configuration
        if (empty($this->apiKey) || $this->apiKey === 'sk-') {
            throw new \RuntimeException('OPENAI_API_KEY is not configured or invalid');
        }

        if (empty($this->model)) {
            throw new \RuntimeException('OPENAI_MODEL is not configured');
        }

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant for a quiz/evaluation platform. Be concise and provide clear answers.'
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500
        ];

        try {
            error_log('OpenAI Request: Model=' . $this->model . ', Message length=' . strlen($message));
            
            $res = $this->http->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . substr($this->apiKey, 0, 20) . '...',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
            ]);

            $statusCode = $res->getStatusCode();
            $response = $res->toArray(false);

            error_log('OpenAI Response Status: ' . $statusCode);
            
            return $response;
            
        } catch (\Exception $e) {
            error_log('OpenAI Exception: ' . $e->getMessage());
            throw new \RuntimeException('OpenAI API Error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function extractText(array $data): string
    {
        // Handle error responses
        if (!empty($data['error'])) {
            return 'Erreur: ' . ($data['error']['message'] ?? 'Une erreur inconnue est survenue');
        }

        // Extract text from chat completion response
        $text = '';
        foreach (($data['choices'] ?? []) as $choice) {
            if (isset($choice['message']['content'])) {
                $text .= $choice['message']['content'];
            }
        }

        return $text !== '' ? trim($text) : 'Pas de réponse disponible';
    }
}
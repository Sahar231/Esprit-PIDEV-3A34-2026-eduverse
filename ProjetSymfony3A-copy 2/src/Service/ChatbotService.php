<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutException;
use Symfony\Contracts\HttpClient\Exception\TransportException;

class ChatbotService
{
    private string $groqApiKey;
    private string $groqApiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(
        private HttpClientInterface $httpClient,
        string $groqApiKey
    ) {
        $this->groqApiKey = $groqApiKey;
    }

    /**
     * Get an answer from the chatbot based on a question and quiz context
     */
    public function getAnswer(string $userQuestion, string $quizContext = '', int $timeout = 10): string
    {
        try {
            // Validate API key
            if (empty($this->groqApiKey) || $this->groqApiKey === 'your-api-key-here') {
                error_log('Chatbot warning: GROQ_API_KEY not configured properly');
                return 'The chatbot is not properly configured. Please contact an administrator.';
            }

            $systemPrompt = "You are a helpful educational assistant. Answer the user's question clearly and concisely. ";
            if ($quizContext) {
                $systemPrompt .= "Related question context: {$quizContext}";
            }

            error_log('Chatbot: Sending request to Groq API for question: ' . substr($userQuestion, 0, 50) . '...');

            $response = $this->httpClient->request('POST', $this->groqApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => $userQuestion,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 512,
                ],
                'timeout' => $timeout,
            ]);

            $statusCode = $response->getStatusCode();
            error_log('Chatbot: Groq API response status: ' . $statusCode);

            if ($statusCode !== 200) {
                try {
                    $errorBody = $response->getContent();
                    error_log('Chatbot error (status ' . $statusCode . '): ' . $errorBody);
                } catch (\Exception $bodyException) {
                    error_log('Chatbot error (status ' . $statusCode . '): Could not read error body');
                }
                
                if ($statusCode === 401) {
                    return 'Authentication error with chatbot service. Please check your API key configuration.';
                } elseif ($statusCode === 400) {
                    return 'Invalid request to chatbot service. The request format may be incorrect.';
                } elseif ($statusCode === 429) {
                    return 'Too many requests to the chatbot service. Please wait a moment and try again.';
                } else {
                    return 'The chatbot service is temporarily unavailable (HTTP ' . $statusCode . '). Please try again later.';
                }
            }

            $data = $response->toArray();
            error_log('Chatbot: API response received successfully');
            
            if (isset($data['choices'][0]['message']['content'])) {
                return $data['choices'][0]['message']['content'];
            }

            error_log('Chatbot error: Unexpected response format from Groq API');
            return 'No response received from the chatbot. Please try again.';
        } catch (TimeoutException $e) {
            error_log('Chatbot timeout error: ' . $e->getMessage());
            return 'The chatbot service is taking too long to respond. Please try again.';
        } catch (TransportException $e) {
            error_log('Chatbot transport error: ' . $e->getMessage());
            return 'Network error connecting to chatbot service. Please check your internet connection.';
        } catch (\Exception $e) {
            error_log('Chatbot error: ' . $e->getMessage() . ' (' . get_class($e) . ')');
            return 'An unexpected error occurred: ' . $e->getMessage();
        }
    }
}

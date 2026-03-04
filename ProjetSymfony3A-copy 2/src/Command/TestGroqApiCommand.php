<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'test:groq-api',
    description: 'Test the Groq API connection and chatbot service'
)]
class TestGroqApiCommand extends Command
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $groqApiKey,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Groq API Connection Test');

        try {
            $io->info('API Key: ' . (isset($this->groqApiKey) && !empty($this->groqApiKey) ? substr($this->groqApiKey, 0, 10) . '...' : 'NOT SET'));
            
            if (!isset($this->groqApiKey) || empty($this->groqApiKey)) {
                $io->error('GROQ_API_KEY environment variable is not set!');
                return Command::FAILURE;
            }

            $io->info('Testing Groq API with a simple request...');
            
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful assistant',
                        ],
                        [
                            'role' => 'user',
                            'content' => 'What is 2+2?',
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 512,
                ],
                'timeout' => 15,
            ]);

            $statusCode = $response->getStatusCode();
            $io->info('Response Status: ' . $statusCode);

            if ($statusCode === 200) {
                $data = $response->toArray();
                $answer = $data['choices'][0]['message']['content'] ?? 'No response';
                $io->success('✓ Groq API is working!');
                $io->section('Response:');
                $io->text($answer);
                return Command::SUCCESS;
            } else {
                $content = $response->getContent(false);
                $io->error([
                    'Groq API returned HTTP ' . $statusCode,
                    'Response body: ' . $content
                ]);
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error([
                'Test failed!',
                'Error: ' . $e->getMessage(),
                'Class: ' . get_class($e),
            ]);

            return Command::FAILURE;
        }
    }
}

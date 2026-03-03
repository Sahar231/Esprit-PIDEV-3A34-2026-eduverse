<?php

namespace App\Service;

class AiQuizGeneratorService
{
    public function __construct(
        private GroqChatService $groq
    ) {}

    /**
     * Generate quiz structure using AI
     */
    public function generateQuiz(string $topic, string $level, int $questionCount): array
    {
        $prompt = $this->buildPrompt($topic, $level, $questionCount);
        
        $response = $this->groq->chat($prompt);
        
        if (isset($response['error'])) {
            throw new \RuntimeException('AI Service Error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        $jsonText = $this->groq->extractText($response);
        
        // Try to extract JSON from the response (in case AI adds extra text)
        if (preg_match('/\{[\s\S]*\}/', $jsonText, $matches)) {
            $jsonText = $matches[0];
        }

        $quizData = json_decode($jsonText, true);
        
        if (!$quizData) {
            throw new \RuntimeException('Failed to parse AI response as JSON. Response: ' . substr($jsonText, 0, 200));
        }

        if (!isset($quizData['questions']) || !is_array($quizData['questions'])) {
            throw new \RuntimeException('Invalid quiz structure: missing "questions" array');
        }

        // Validate questions
        foreach ($quizData['questions'] as $index => $question) {
            $this->validateQuestion($question, $index);
        }

        return $quizData;
    }

    private function buildPrompt(string $topic, string $level, int $questionCount): string
    {
        $levelDescription = match($level) {
            'facile' => 'very easy, basic concepts only',
            'intermediaire' => 'intermediate, requires some understanding',
            'difficile' => 'advanced, requires deep knowledge',
            default => 'intermediate'
        };

        return <<<PROMPT
You are an expert quiz generator. Generate a JSON quiz with EXACTLY $questionCount questions about "$topic" at $levelDescription level.

IMPORTANT: Return ONLY valid JSON, no additional text before or after.

JSON Schema:
{
  "questions": [
    {
      "text": "Question text in French",
      "choices": ["Choice 1", "Choice 2", "Choice 3", "Choice 4"],
      "answerIndex": 0,
      "explanation": "Why this answer is correct"
    }
  ]
}

Requirements:
- EXACTLY $questionCount questions
- EXACTLY 4 choices per question
- answerIndex must be 0, 1, 2, or 3
- All text MUST be in French
- Explanations should be clear and educational
- Return ONLY the JSON object, nothing else

Generate the quiz now:
PROMPT;
    }

    private function validateQuestion(array $question, int $index): void
    {
        if (!isset($question['text']) || empty($question['text'])) {
            throw new \RuntimeException("Question $index: missing or empty 'text'");
        }

        if (!isset($question['choices']) || !is_array($question['choices'])) {
            throw new \RuntimeException("Question $index: missing or invalid 'choices'");
        }

        if (count($question['choices']) !== 4) {
            throw new \RuntimeException("Question $index: must have exactly 4 choices, got " . count($question['choices']));
        }

        if (!isset($question['answerIndex']) || !is_numeric($question['answerIndex'])) {
            throw new \RuntimeException("Question $index: missing or invalid 'answerIndex'");
        }

        $answerIndex = (int)$question['answerIndex'];
        if ($answerIndex < 0 || $answerIndex > 3) {
            throw new \RuntimeException("Question $index: answerIndex must be 0-3, got $answerIndex");
        }

        if (!isset($question['explanation']) || empty($question['explanation'])) {
            throw new \RuntimeException("Question $index: missing or empty 'explanation'");
        }
    }
}

<?php

namespace App\Controller\Teacher;

use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Reponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

#[Route('/teacher/quizzes/ai', name: 'teacher_quiz_ai_')]
// removed role restriction, anyone can access (adjust as needed)
class AiQuizController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validate input
            $title = trim($data['title'] ?? '');
            $topic = trim($data['topic'] ?? '');
            $level = trim($data['level'] ?? '');
            $count = (int)($data['count'] ?? 0);
            $duration = (int)($data['duration'] ?? 30);

            if (!$title) {
                return $this->json(['error' => 'Title is required'], 400);
            }

            if (!$topic) {
                return $this->json(['error' => 'Topic is required'], 400);
            }

            if (!in_array($level, ['facile', 'intermediaire', 'difficile'])) {
                return $this->json(['error' => 'Invalid level. Must be: facile, intermediaire, difficile'], 400);
            }

            if ($count < 1 || $count > 50) {
                return $this->json(['error' => 'Question count must be between 1 and 50'], 400);
            }

            if ($duration < 1 || $duration > 300) {
                return $this->json(['error' => 'Duration must be between 1 and 300 minutes'], 400);
            }

            // Call Python script to generate quiz
            $quizData = $this->generateQuizWithPython($topic, $level, $count);

            if (count($quizData['questions']) !== $count) {
                return $this->json([
                    'error' => "Generated " . count($quizData['questions']) . 
                              " questions instead of $count"
                ], 400);
            }

            // Create Quiz entity
            $quiz = new Quiz();
            $quiz->setTitle($title);
            $quiz->setLevel($level);
            $quiz->setDuration($duration);
            $quiz->setStatus('DRAFT');
            $quiz->setInstructor($this->getUser());
            $quiz->setDescription("Auto-generated quiz about: $topic");

            // Create Questions and Reponses
            foreach ($quizData['questions'] as $qData) {
                $question = new Question();
                $question->setText($qData['text']);
                $question->setExplanation($qData['explanation']);
                $question->setQuiz($quiz);

                $answerIndex = (int)$qData['answerIndex'];

                foreach ($qData['choices'] as $choiceIndex => $choiceText) {
                    $reponse = new Reponse();
                    $reponse->setContent($choiceText);
                    $reponse->setIsCorrect($choiceIndex === $answerIndex);
                    $reponse->setQuestion($question);
                    
                    $question->addReponse($reponse);
                }

                $quiz->addQuestion($question);
            }

            // Save to database
            $em->persist($quiz);
            $em->flush();

            return $this->json([
                'success' => true,
                'quizId' => $quiz->getId(),
                'createdQuestions' => count($quizData['questions']),
                'message' => "Quiz created successfully with " . count($quizData['questions']) . " questions"
            ], 201);

        } catch (\Throwable $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'success' => false
            ], 400);
        }
    }

    /**
     * Call Python script to generate quiz locally (no API)
     */
    private function generateQuizWithPython(string $topic, string $level, int $count): array
    {
        // Get the project root directory
        $projectDir = dirname(__DIR__, 3);
        $scriptPath = $projectDir . '/ai_quiz_generator.py';

        if (!file_exists($scriptPath)) {
            throw new \RuntimeException('ai_quiz_generator.py not found at ' . $scriptPath);
        }

        // Find Python executable
        $pythonBinary = $this->findPythonBinary();
        if (!$pythonBinary) {
            throw new \RuntimeException(
                'Python not found. Please install Python 3.7+ and add it to PATH. ' .
                'Or set PYTHON_BINARY environment variable.'
            );
        }

        // Build command to execute Python script
        $process = new Process([
            $pythonBinary,
            $scriptPath,
            '--topic', $topic,
            '--level', $level,
            '--count', (string)$count,
            '--template-only' // Use templates, don't require Ollama
        ]);

        $process->setTimeout(120); // 2 minutes timeout
        
        try {
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            $quizData = json_decode($output, true);

            if (!$quizData || !isset($quizData['questions'])) {
                throw new \RuntimeException('Invalid JSON from Python script: ' . substr($output, 0, 200));
            }

            return $quizData;

        } catch (ProcessFailedException $e) {
            throw new \RuntimeException('Python script error: ' . $e->getProcess()->getErrorOutput());
        }
    }

    /**
     * Find Python executable in system PATH
     */
    private function findPythonBinary(): ?string
    {
        // Check environment variable first
        $envPython = getenv('PYTHON_BINARY');
        if ($envPython && is_executable($envPython)) {
            return $envPython;
        }

        // Try common Python commands
        $candidates = ['python3', 'python', 'python.exe', 'python3.exe'];
        
        foreach ($candidates as $cmd) {
            // Use 'where' on Windows, 'which' on Linux/Mac
            $findCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "where $cmd" : "which $cmd";
            
            $output = shell_exec($findCmd . ' 2>nul');
            if ($output) {
                $path = trim(explode("\n", $output)[0]); // Get first result
                if (is_executable($path)) {
                    return $path;
                }
            }
        }

        // Check if directly executable
        foreach ($candidates as $cmd) {
            if (is_executable($cmd)) {
                return $cmd;
            }
        }

        return null;
    }
}


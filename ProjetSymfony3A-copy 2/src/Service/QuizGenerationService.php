<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class QuizGenerationService
{
    private string $pythonPath;
    private string $scriptPath;

    public function __construct(string $pythonPath = 'python', string $projectDir = null, string $scriptPath = null)
    {
        // Allow override of python executable via DSN or env if needed
        $this->pythonPath = $pythonPath;
        if (null !== $scriptPath) {
            $this->scriptPath = $scriptPath;
        } else {
            // compute default script location relative to project root
            $base = $projectDir ?? (__DIR__ . '/../../');
            $this->scriptPath = rtrim($base, '/\\') . '/python/quiz_generator.py';
        }
    }

    /**
     * @return array{title:string,count:int,questions:array}
     *
     * @throws \RuntimeException when the generator fails
     */
    public function generateFromChapter(string $title, string $text = '', int $n = 10, int $seed = 42): array
    {
        $cmd = [
            $this->pythonPath,
            $this->scriptPath,
            '--title', $title,
            '--text', $text,
            '--n', (string)$n,
            '--seed', (string)$seed,
        ];

        $process = new Process($cmd);
        $process->setTimeout(60);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new \RuntimeException("Quiz generator failed: " . $e->getMessage());
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            throw new \RuntimeException('Quiz generator returned empty output');
        }

        $data = json_decode($output, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid JSON returned by quiz generator: ' . $output);
        }

        return $data;
    }
}

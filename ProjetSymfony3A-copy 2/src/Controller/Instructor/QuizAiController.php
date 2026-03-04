<?php

namespace App\Controller\Instructor;

use App\Service\QuizGenerationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizAiController extends AbstractController
{
    private QuizGenerationService $generator;

    public function __construct(QuizGenerationService $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @Route("/instructor/quiz/ai", name="instructor_quiz_ai")
     */
    public function index(Request $request): Response
    {
        $data = null;
        $errors = [];

        if ($request->isMethod('POST')) {
            $title = trim((string)$request->request->get('title', ''));
            $text = trim((string)$request->request->get('text', ''));
            $n = (int)$request->request->get('n', 10);
            $seed = (int)$request->request->get('seed', 42);

            if ($title === '') {
                $errors[] = 'Please provide a title for the chapter.';
            }

            if (empty($errors)) {
                try {
                    $data = $this->generator->generateFromChapter($title, $text, $n, $seed);
                } catch (\RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return $this->render('instructor/quiz_ai.html.twig', [
            'quiz' => $data,
            'errors' => $errors,
            'form' => [
                'title' => $request->request->get('title', ''),
                'text' => $request->request->get('text', ''),
                'n' => $request->request->get('n', 10),
                'seed' => $request->request->get('seed', 42),
            ],
        ]);
    }
}

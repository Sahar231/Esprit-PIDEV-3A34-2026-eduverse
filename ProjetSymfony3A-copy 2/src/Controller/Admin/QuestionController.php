<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\Quizfor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/question')]
#[IsGranted('ROLE_ADMIN')]
class QuestionController extends AbstractController
{
    #[Route('/add', name: 'admin_question_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quizId = $request->query->get('quizId');
        $quiz = $entityManager->getRepository(Quizfor::class)->find($quizId);

        if (!$quiz) {
            $this->addFlash('error', 'Quiz not found');
            return $this->redirectToRoute('admin_quiz_list');
        }

        $question = new Question();
        $question->setQuiz($quiz);

        if ($request->isMethod('POST')) {
            $questionText = $request->request->get('question');
            $reply = $request->request->get('reply');
            $score = $request->request->get('score') ?? 1;
            $type = $request->request->get('type') ?? 'multiple_choice';
            $choices = $request->request->get('choices');

            // Validate question text
            if (!$questionText || strlen(trim($questionText)) < 5) {
                $this->addFlash('error', 'Question text must be at least 5 characters');
                return $this->redirectToRoute('admin_question_add', ['quizId' => $quiz->getId()]);
            }

            // Validate answer
            if (!$reply || strlen(trim($reply)) < 1) {
                $this->addFlash('error', 'Correct answer/reply is required');
                return $this->redirectToRoute('admin_question_add', ['quizId' => $quiz->getId()]);
            }

            $question->setQuestion($questionText);
            $question->setReply($reply);
            $question->setScore($score);
            $question->setType($type);
            
            // Handle choices if it's a multiple choice question
            if ($type === 'multiple_choice' && is_array($choices)) {
                // Filter out empty choices
                $choices = array_filter($choices, function($choice) {
                    return !empty(trim($choice));
                });
                
                if (count($choices) < 2) {
                    $this->addFlash('error', 'Please add at least 2 answer choices for multiple choice questions');
                    return $this->redirectToRoute('admin_question_add', ['quizId' => $quiz->getId()]);
                }
                
                $question->setChoices(array_values($choices));
            }

            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash('success', 'Question added successfully!');
            return $this->redirectToRoute('admin_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('admin/question/add.html.twig', [
            'quiz' => $quiz,
            'question' => $question
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_question_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $question = $entityManager->getRepository(Question::class)->find($id);

        if (!$question) {
            $this->addFlash('error', 'Question not found');
            return $this->redirectToRoute('admin_quiz_list');
        }

        $quiz = $question->getQuiz();

        if ($request->isMethod('POST')) {
            $questionText = $request->request->get('question');
            $reply = $request->request->get('reply');
            $score = $request->request->get('score') ?? 1;
            $type = $request->request->get('type') ?? 'multiple_choice';
            $choices = $request->request->get('choices');

            // Validate question text
            if (!$questionText || strlen(trim($questionText)) < 5) {
                $this->addFlash('error', 'Question text must be at least 5 characters');
                return $this->redirectToRoute('admin_question_edit', ['id' => $id]);
            }

            // Validate answer
            if (!$reply || strlen(trim($reply)) < 1) {
                $this->addFlash('error', 'Correct answer/reply is required');
                return $this->redirectToRoute('admin_question_edit', ['id' => $id]);
            }

            $question->setQuestion($questionText);
            $question->setReply($reply);
            $question->setScore($score);
            $question->setType($type);

            // Handle choices if it's a multiple choice question
            if ($type === 'multiple_choice' && is_array($choices)) {
                // Filter out empty choices
                $choices = array_filter($choices, function($choice) {
                    return !empty(trim($choice));
                });
                
                if (count($choices) < 2) {
                    $this->addFlash('error', 'Please add at least 2 answer choices for multiple choice questions');
                    return $this->redirectToRoute('admin_question_edit', ['id' => $id]);
                }
                
                $question->setChoices(array_values($choices));
            } else if ($type !== 'multiple_choice') {
                // Clear choices if changing from multiple choice to another type
                $question->setChoices(null);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Question updated successfully!');
            return $this->redirectToRoute('admin_quiz_edit', ['id' => $quiz->getId()]);
        }

        return $this->render('admin/question/edit.html.twig', [
            'question' => $question,
            'quiz' => $quiz
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_question_delete', methods: ['POST', 'GET'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $question = $entityManager->getRepository(Question::class)->find($id);

        if (!$question) {
            $this->addFlash('error', 'Question not found');
            return $this->redirectToRoute('admin_quiz_list');
        }

        $quiz = $question->getQuiz();
        $quizId = $quiz->getId();

        $entityManager->remove($question);
        $entityManager->flush();

        $this->addFlash('success', 'Question deleted successfully!');
        return $this->redirectToRoute('admin_quiz_edit', ['id' => $quizId]);
    }
}

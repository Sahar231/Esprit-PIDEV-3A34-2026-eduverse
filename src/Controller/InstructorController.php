<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/instructor', name: 'instructor_')]
class InstructorController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('instructor/instructor-dashboard.html.twig');
    }

    #[Route('/list', name: 'list')]
    public function list(): Response
    {
        return $this->render('instructor/instructor-list.html.twig');
    }

    #[Route('/create-course', name: 'create_course')]
    public function createCourse(): Response
    {
        return $this->render('instructor/instructor-create-course.html.twig');
    }

    #[Route('/manage-courses', name: 'manage_courses')]
    public function manageCourses(): Response
    {
        return $this->render('instructor/instructor-manage-course.html.twig');
    }

    #[Route('/quiz', name: 'quiz')]
    public function quiz(QuizRepository $quizRepository): Response
    {
        $quizzes = $quizRepository->findAll();
        return $this->render('instructor/instructor-quiz.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }

    #[Route('/quiz-setup-test', name: 'quiz_setup_test_data')]
    public function setupTestData(EntityManagerInterface $em): Response
    {
        // Vérifier s'il y a déjà des quizzes
        $existingQuizzes = $em->getRepository(Quiz::class)->findAll();
        if (!empty($existingQuizzes)) {
            return new Response('<h2>Les données de test existent déjà! Allez à <a href="/instructor/quiz">/instructor/quiz</a></h2>');
        }

        // Quiz 1: Quiz Sécurité Informatique
        $quiz1 = new Quiz();
        $quiz1->setTitle('Quiz Sécurité Informatique');
        $quiz1->setLevel('intermediaire');
        $quiz1->setDuration(30);

        $question1 = new Question();
        $question1->setText('Qu est-ce que la sécurité informatique ?');
        $question1->setQuiz($quiz1);
        $quiz1->addQuestion($question1);
        
        $response1 = new Reponse();
        $response1->setContent('L ensemble des mesures pour protéger les données');
        $response1->setIsCorrect(true);
        $response1->setQuestion($question1);
        $question1->addReponse($response1);
        
        $response2 = new Reponse();
        $response2->setContent('Un type d ordinateur');
        $response2->setIsCorrect(false);
        $response2->setQuestion($question1);
        $question1->addReponse($response2);

        // Quiz 2: Quiz Marketing Digital
        $quiz2 = new Quiz();
        $quiz2->setTitle('Quiz Marketing Digital');
        $quiz2->setLevel('facile');
        $quiz2->setDuration(20);

        $question2 = new Question();
        $question2->setText('Qu est-ce que le SEO ?');
        $question2->setQuiz($quiz2);
        $quiz2->addQuestion($question2);
        
        $response3 = new Reponse();
        $response3->setContent('Search Engine Optimization');
        $response3->setIsCorrect(true);
        $response3->setQuestion($question2);
        $question2->addReponse($response3);
        
        $response4 = new Reponse();
        $response4->setContent('Social Engine Optimization');
        $response4->setIsCorrect(false);
        $response4->setQuestion($question2);
        $question2->addReponse($response4);

        // Quiz 3: Quiz Python Avancé
        $quiz3 = new Quiz();
        $quiz3->setTitle('Quiz Python Avancé');
        $quiz3->setLevel('difficile');
        $quiz3->setDuration(45);

        $question3 = new Question();
        $question3->setText('Qu est-ce qu un décorateur en Python ?');
        $question3->setQuiz($quiz3);
        $quiz3->addQuestion($question3);
        
        $response5 = new Reponse();
        $response5->setContent('Une fonction qui modifie le comportement d une autre fonction');
        $response5->setIsCorrect(true);
        $response5->setQuestion($question3);
        $question3->addReponse($response5);
        
        $response6 = new Reponse();
        $response6->setContent('Un type de variable');
        $response6->setIsCorrect(false);
        $response6->setQuestion($question3);
        $question3->addReponse($response6);

        // Persist everything
        $em->persist($quiz1);
        $em->persist($quiz2);
        $em->persist($quiz3);
        $em->flush();

        return new Response('<h2>✅ 3 quizzes de test ajoutés avec succès!</h2><p><a href="/instructor/quiz">Voir la liste des quiz</a></p>');
    }

    #[Route('/reviews', name: 'reviews')]
    public function reviews(): Response
    {
        return $this->render('instructor/instructor-review.html.twig');
    }

    #[Route('/earnings', name: 'earnings')]
    public function earnings(): Response
    {
        return $this->render('instructor/instructor-earning.html.twig');
    }

    #[Route('/payout', name: 'payout')]
    public function payout(): Response
    {
        return $this->render('instructor/instructor-payout.html.twig');
    }

    #[Route('/orders', name: 'orders')]
    public function orders(): Response
    {
        return $this->render('instructor/instructor-order.html.twig');
    }

    #[Route('/students', name: 'students')]
    public function students(): Response
    {
        return $this->render('instructor/instructor-studentlist.html.twig');
    }

    #[Route('/edit-profile', name: 'edit_profile')]
    public function editProfile(): Response
    {
        return $this->render('instructor/instructor-edit-profile.html.twig');
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('instructor/instructor-setting.html.twig');
    }

    #[Route('/delete-account', name: 'delete_account')]
    public function deleteAccount(): Response
    {
        return $this->render('instructor/instructor-delete-account.html.twig');
    }

    #[Route('/{id<\d+>}', name: 'detail')]
    public function detail(int $id): Response
    {
        return $this->render('instructor/instructor-single.html.twig', [
            'instructorId' => $id,
        ]);
    }
}

<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Handle Face Descriptor manually because it's not managed by the form to avoid type errors
            $faceData = $request->request->get('faceDescriptor');
            if ($faceData) {
                // Decode JSON string to an array before setting it to the entity
                $user->setFaceDescriptor(json_decode($faceData, true));
            }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsBlocked(false);

            // Handle Role and Approval
            $roleInput = $form->get('role')->getData();
            if ($roleInput === 'instructor') {
                $user->setRoles(['ROLE_INSTRUCTOR']);
                $user->setRole('Instructor');
            } else {
                $user->setRoles(['ROLE_STUDENT']);
                $user->setRole('Student');
            }
            $user->setIsApproved(false); // Everyone needs approval now

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie. Votre compte est en attente d\'approbation.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}

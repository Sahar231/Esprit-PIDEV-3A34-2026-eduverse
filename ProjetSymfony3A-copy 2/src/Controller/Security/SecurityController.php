<?php

namespace App\Controller\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/emergency-admin', name: 'app_emergency_admin')]
    public function emergencyAdmin(EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $email = 'admin@test.com';
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setFullName('Super Admin');
            $user->setCreatedAt(new \DateTimeImmutable());
        }
        
        $user->setPassword($hasher->hashPassword($user, 'admin123'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setRole('Admin');
        $user->setIsApproved(true);
        $user->setIsBlocked(false);
        
        $em->persist($user);
        $em->flush();
        
        return new Response('Admin account updated/created. Email: admin@test.com, Password: admin123. <a href="/login">Go to login</a>');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

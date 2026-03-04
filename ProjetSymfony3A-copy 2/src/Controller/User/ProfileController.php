<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/student/profile/edit', name: 'student_profile_edit')]
    #[IsGranted('ROLE_STUDENT')]
    public function studentProfileEdit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('student_profile_edit');
        }

        return $this->render('student/student-edit-profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/instructor/profile/edit', name: 'instructor_edit_profile')]
    #[IsGranted('ROLE_INSTRUCTOR')]
    public function instructorProfileEdit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('instructor_edit_profile');
        }

        return $this->render('instructor/instructor-edit-profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }
}

<?php

namespace App\Controller\Security;

use App\Repository\UserRepository;
use App\Service\FaceRecognitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FaceAuthController extends AbstractController
{
    #[Route('/login/face', name: 'app_face_login', methods: ['GET'])]
    public function faceLogin(): Response
    {
        return $this->render('security/face_login.html.twig');
    }

    #[Route('/login/face/check', name: 'app_face_login_check', methods: ['POST'])]
    public function faceLoginCheck(Request $request, UserRepository $userRepository, FaceRecognitionService $faceRecognitionService, Security $security): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $liveDescriptor = $data['descriptor'] ?? null;

        if (!$liveDescriptor || !is_array($liveDescriptor)) {
            return new JsonResponse(['error' => 'Veuillez fournir un descripteur biométrique valide.'], 400);
        }

        // Retrieve all users who have registered a face descriptor
        $users = $userRepository->createQueryBuilder('u')
            ->where('u.faceDescriptor IS NOT NULL')
            ->getQuery()
            ->getResult();

        $matchedUser = null;
        $bestDistance = 1.0;

        foreach ($users as $user) {
            $storedDescriptor = $user->getFaceDescriptor();
            if ($storedDescriptor) {
                try {
                    $distance = $faceRecognitionService->euclideanDistance($storedDescriptor, $liveDescriptor);
                    // Standard threshold for face-api.js SSD is 0.6
                    if ($distance < 0.6 && $distance < $bestDistance) {
                        $bestDistance = $distance;
                        $matchedUser = $user;
                    }
                } catch (\Exception $e) {
                    continue; // Skip invalid formats
                }
            }
        }

        if ($matchedUser) {
            // Vérification manuelle des statuts du compte pour répliquer le comportement du UserChecker
            if ($matchedUser->isRejected()) {
                return new JsonResponse(['error' => 'Votre compte a été rejeté par l\'administrateur.'], 403);
            }
            if ($matchedUser->isBlocked()) {
                return new JsonResponse(['error' => 'Votre compte est bloqué. Veuillez contacter l\'administrateur.'], 403);
            }
            if (!$matchedUser->isApproved()) {
                return new JsonResponse(['error' => 'Votre compte est en attente d\'approbation par l\'administrateur.'], 403);
            }

            // Log in the user manually via Symfony bundle Security
            try {
                $security->login($matchedUser, \App\Security\AppAuthenticator::class);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => $e->getMessage()], 403);
            }

            // Determine redirect path
            $roles = $matchedUser->getRoles();
            $redirectPath = 'student_dashboard'; // default
            if (in_array('ROLE_ADMIN', $roles)) {
                $redirectPath = 'admin_dashboard';
            } elseif (in_array('ROLE_INSTRUCTOR', $roles)) {
                $redirectPath = 'instructor_dashboard';
            }

            return new JsonResponse([
                'success' => true,
                'redirect' => $this->generateUrl($redirectPath),
                'message' => 'Authentification biométrique réussie ! Bienvenue ' . $matchedUser->getFullName()
            ]);
        }

        return new JsonResponse(['error' => 'Aucun visage correspondant n\'a été trouvé dans notre base de données sécurisée.'], 403);
    }
}

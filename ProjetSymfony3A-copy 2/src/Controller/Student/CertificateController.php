<?php

namespace App\Controller\Student;

use App\Entity\Certificate;
use App\Repository\CertificateRepository;
use App\Service\CertificatePdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/certificates', name: 'student_certificate_')]
#[IsGranted('ROLE_STUDENT')]
class CertificateController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CertificateRepository $certificateRepository): Response
    {
        $user = $this->getUser();
        // getUser() returns UserInterface|null; ensure it's the actual User entity
        assert($user instanceof \App\Entity\User);
        $certificates = $certificateRepository->findByUser($user);

        return $this->render('student/certificate/index.html.twig', [
            'certificates' => $certificates,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Certificate $certificate): Response
    {
        // Verify the certificate belongs to the current user
        $user = $this->getUser();
        assert($user instanceof \App\Entity\User);
        if ($certificate->getUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have access to this certificate.');
        }

        return $this->render('student/certificate/show.html.twig', [
            'certificate' => $certificate,
        ]);
    }

    #[Route('/{id}/download', name: 'download', methods: ['GET'])]
    public function download(Certificate $certificate, CertificatePdfService $pdfService): Response
    {
        // Verify the certificate belongs to the current user
        if ($certificate->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to this certificate.');
        }

        // Generate PDF
        $pdfContent = $pdfService->generatePdf($certificate);
        $filename = $pdfService->getFilename($certificate);

        // Return as downloadable PDF
        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

    #[Route('/{id}/print', name: 'print', methods: ['GET'])]
    public function print(Certificate $certificate): Response
    {
        // Verify the certificate belongs to the current user
        if ($certificate->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to this certificate.');
        }

        // Show print-friendly version (browser will handle print dialog)
        return $this->render('student/certificate/print.html.twig', [
            'certificate' => $certificate,
        ]);
    }
}

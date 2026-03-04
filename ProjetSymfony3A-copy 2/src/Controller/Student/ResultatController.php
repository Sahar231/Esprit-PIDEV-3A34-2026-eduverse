<?php

namespace App\Controller\Student;

use App\Entity\Resultat;
use App\Repository\ResultatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/resultat')]
#[IsGranted('ROLE_STUDENT')]
class ResultatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ResultatRepository $resultatRepository,
    ) {
    }

    /**
     * Download quiz result as PDF
     */
    #[Route('/{id}/pdf', name: 'student_resultat_pdf', methods: ['GET'])]
    public function downloadPdf(Resultat $resultat): Response
    {
        // Verify that the result belongs to the current user
        if ($resultat->getStudent() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only download your own results.');
        }

        // Render the Twig template to HTML
        $html = $this->renderView('pdf/student_result.html.twig', [
            'resultat' => $resultat,
            'quiz' => $resultat->getQuiz(),
            'student' => $resultat->getStudent(),
            'answers' => $resultat->getAnswers() ?? [],
        ]);

        // Configure Dompdf options
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', __DIR__ . '/../../public');

        // Initialize Dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate filename
        $filename = sprintf(
            'resultat-quiz-%d-%s.pdf',
            $resultat->getId(),
            $resultat->getCreatedAt()?->format('Y-m-d-His') ?? 'export'
        );

        // Return PDF response
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($dompdf->output()),
            ]
        );
    }

    /**
     * View detailed result with answers
     */
    #[Route('/{id}', name: 'student_resultat_show', methods: ['GET'])]
    public function show(Resultat $resultat): Response
    {
        // Verify that the result belongs to the current user
        if ($resultat->getStudent() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only view your own results.');
        }

        return $this->render('student/resultat/show.html.twig', [
            'resultat' => $resultat,
            'quiz' => $resultat->getQuiz(),
            'answers' => $resultat->getAnswers() ?? [],
        ]);
    }
}

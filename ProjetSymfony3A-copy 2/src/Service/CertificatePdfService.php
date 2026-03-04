<?php

namespace App\Service;

use App\Entity\Certificate;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class CertificatePdfService
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    /**
     * Generate a PDF certificate and return as binary content
     */
    public function generatePdf(Certificate $certificate): string
    {
        // Render the certificate template to HTML
        $html = $this->twig->render('student/certificate/pdf.html.twig', [
            'certificate' => $certificate,
            'isPdf' => true, // Flag to indicate we're rendering for PDF
        ]);

        // Configure DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultPaperSize', 'A4');
        $options->set('defaultPaperOrientation', 'landscape');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Generate certificate filename
     */
    public function getFilename(Certificate $certificate): string
    {
        $sanitizedTitle = preg_replace('/[^a-zA-Z0-9_-]/', '-', $certificate->getFormation()->getTitle());
        return "certificate-{$sanitizedTitle}-" . $certificate->getId() . '.pdf';
    }
}

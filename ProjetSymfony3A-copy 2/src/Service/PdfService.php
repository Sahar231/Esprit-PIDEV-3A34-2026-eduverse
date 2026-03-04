<?php

namespace App\Service;

use App\Entity\Club;
use App\Entity\Event;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PdfService
{
    private Dompdf $domPdf;
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $this->domPdf = new Dompdf($pdfOptions);
        $this->twig = $twig;
    }

    public function generateBinaryPDF(string $html): string
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        return $this->domPdf->output();
    }

    public function showPdfFile(string $html): void
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        $this->domPdf->stream("document.pdf", [
            "Attachment" => false
        ]);
    }

    /**
     * Generate PDF for a club
     */
    public function generateClubPdf(Club $club): Response
    {
        $html = $this->twig->render('pdf/club.html.twig', [
            'club' => $club,
        ]);

        $this->domPdf->loadHtml($html);
        $this->domPdf->render();

        return new Response(
            $this->domPdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="club_' . $club->getId() . '_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    /**
     * Generate PDF for an event
     */
    public function generateEventPdf(Event $event): Response
    {
        $html = $this->twig->render('pdf/event.html.twig', [
            'event' => $event,
            'club' => $event->getClub(),
        ]);

        $this->domPdf->loadHtml($html);
        $this->domPdf->render();

        return new Response(
            $this->domPdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="event_' . $event->getId() . '_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    /**
     * Generate PDF list of all clubs
     *
     * @param Club[] $clubs
     */
    public function generateClubsListPdf(array $clubs): Response
    {
        $html = $this->twig->render('pdf/clubs_list.html.twig', [
            'clubs' => $clubs,
        ]);

        $this->domPdf->loadHtml($html);
        $this->domPdf->render();

        return new Response(
            $this->domPdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="clubs_list_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    /**
     * Generate PDF list of all events
     *
     * @param Event[] $events
     */
    public function generateEventsListPdf(array $events): Response
    {
        $html = $this->twig->render('pdf/events_list.html.twig', [
            'events' => $events,
        ]);

        $this->domPdf->loadHtml($html);
        $this->domPdf->render();

        return new Response(
            $this->domPdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="events_list_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }
}

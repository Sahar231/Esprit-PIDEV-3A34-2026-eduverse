<?php

namespace App\Controller\Admin;

use App\Service\AdminAnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/admin/analytics', name: 'admin_analytics_')]
#[IsGranted('ROLE_ADMIN')]
class AnalyticsController extends AbstractController
{
    public function __construct(
        private AdminAnalyticsService $analyticsService,
        private HttpClientInterface $httpClient,
    ) {
    }

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        // Get analytics data
        $analyticsData = $this->analyticsService->getAnalytics();

        // Get AI insights
        $insights = $this->getAiInsights($analyticsData);

        return $this->render('admin/analytics/dashboard.html.twig', [
            'metrics' => $analyticsData,
            'insights' => $insights,
        ]);
    }

    /**
     * Generate AI insights from analytics data using Groq API
     *
     * @param array<string,mixed> $analyticsData
     */
    private function getAiInsights(array $analyticsData): string
    {
        $apiKey = $_ENV['GROQ_API_KEY'] ?? null;

        if (!$apiKey || strpos($apiKey, 'your_key') !== false || $apiKey === 'gsk_') {
            return 'AI insights unavailable - API key not configured.';
        }

        // Format data for AI analysis
        $topFormation = $analyticsData['topFormations'][0] ?? null;
        $formationsList = implode(', ', array_map(fn($f) => $f['name'] . ' (€' . $f['revenue'] . ')', $analyticsData['topFormations']));

        $prompt = <<<PROMPT
Analyze this education platform analytics and provide 3 key business insights in French:

📊 METRICS:
- Total Revenue (30 days): €{$analyticsData['totalRevenue']}
- Average Dropout Rate: {$analyticsData['avgDropoutRate']}%
- Total Credits Sold: {$analyticsData['totalCreditsSold']['credits']} credits (€{$analyticsData['totalCreditsSold']['revenue']})

🏆 TOP FORMATIONS:
{$formationsList}

Please provide 3 actionable insights for administrators:
1. Top performing formation and why
2. Dropout rate concern (if high, how to reduce it)
3. Recommendation for growth

Keep each insight to 1-2 sentences. Be concise and professional.
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ],
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() === 200) {
                $result = $response->toArray();
                return $result['choices'][0]['message']['content'] ?? 'Unable to generate insights.';
            }
        } catch (\Exception $e) {
            error_log('AI insights error: ' . $e->getMessage());
        }

        return 'Unable to generate AI insights at this moment.';
    }
}

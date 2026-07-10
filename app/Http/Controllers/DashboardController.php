<?php

namespace App\Http\Controllers;

use App\Support\PaginationPerPage;
use App\Services\DashboardMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardMetricsService $metrics): View|JsonResponse
    {
        $user = auth()->user();
        $search = $request->query('search');
        $search = is_string($search) ? $search : null;
        $topInvestorsPerPage = PaginationPerPage::resolve($request, 'top_investors_per_page', 5);
        $personal = $metrics->personalSummary($user);
        $platform = $metrics->platformSummary();

        if ($request->ajax() && $request->query('ajax_fragment') === 'top_investors') {
            $topInvestors = $metrics->topInvestorsByProfitGlobally($topInvestorsPerPage, $search);

            return response()->json([
                'html' => view('dashboard.partials.top-investors-fragment', compact('topInvestors'))->render(),
            ]);
        }

        return view('dashboard', [
            'platform' => $platform,
            'personal' => $personal,
            'topInvestors' => $metrics->topInvestorsByProfitGlobally($topInvestorsPerPage, $search),
            'charts' => $this->chartPayload($metrics, $platform),
        ]);
    }

    /**
     * @param  array<string, mixed>  $platform
     * @return array<string, mixed>
     */
    private function chartPayload(
        DashboardMetricsService $metrics,
        array $platform,
    ): array {
        return [
            'profitTrend' => $metrics->monthlyProfitTrendPlatform(),
            'capitalVsProfit' => [
                'capital' => (float) ($platform['total_contributions'] ?? 0),
                'profit' => (float) ($platform['total_profit_distributed'] ?? 0),
            ],
            'investmentStatus' => $metrics->investmentStatusBreakdown(),
            'monthlyPayments' => $metrics->currentMonthPaymentSummary(),
        ];
    }
}

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

        if ($request->ajax() && $request->query('ajax_fragment') === 'top_investors') {
            $topInvestors = $user->isAdmin()
                ? $metrics->topInvestorsByProfitGlobally($topInvestorsPerPage, $search)
                : $metrics->topCoInvestorsByProfit($user, $topInvestorsPerPage, $search);

            return response()->json([
                'html' => view('dashboard.partials.top-investors-fragment', compact('topInvestors'))->render(),
            ]);
        }

        return view('dashboard', [
            'platform' => $user->isAdmin() ? $metrics->platformSummary() : null,
            'personal' => $metrics->personalSummary($user),
            'topInvestors' => $user->isAdmin()
                ? $metrics->topInvestorsByProfitGlobally($topInvestorsPerPage, $search)
                : $metrics->topCoInvestorsByProfit($user, $topInvestorsPerPage, $search),
            'topInvestorsScope' => $user->isAdmin() ? 'global' : 'shared',
        ]);
    }
}

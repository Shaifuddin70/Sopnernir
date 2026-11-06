<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialInstallment;
use App\Models\User;
use App\Services\SpecialInstallmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialInstallmentController extends Controller
{
    public function index(): View
    {
        $installments = SpecialInstallment::query()
            ->withCount(['payments as paid_count' => fn ($query) => $query->whereNotNull('paid_at')])
            ->latest()
            ->get();

        $activeUsers = User::query()->where('is_active', true)->count();

        return view('admin.special-installments.index', compact('installments', 'activeUsers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $installment = SpecialInstallment::query()->create([
            'title' => trim($validated['title']),
            'amount' => number_format((float) $validated['amount'], 2, '.', ''),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.special-installments.show', $installment)
            ->with('status', __('Special installment created.'));
    }

    public function show(Request $request, SpecialInstallment $specialInstallment, SpecialInstallmentService $installments): View|JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $allRows = $installments->rows($specialInstallment);
        $summaryPaid = $allRows->where('is_paid', true)->count();
        $summaryTotal = $allRows->count();
        $summaryUnpaid = $summaryTotal - $summaryPaid;
        $rows = $allRows;

        if ($search !== '') {
            $needle = strtolower($search);
            $rows = $rows->filter(function (array $row) use ($needle): bool {
                return str_contains(strtolower($row['name']), $needle)
                    || str_contains(strtolower($row['email']), $needle)
                    || str_contains(strtolower((string) ($row['phone'] ?? '')), $needle);
            })->values();
        }

        $filteredCount = $rows->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin.special-installments.partials.table-fragment', compact(
                    'rows',
                    'specialInstallment',
                    'filteredCount',
                    'summaryTotal',
                ))->render(),
                'summary_html' => view('admin.special-installments.partials.summary', compact(
                    'summaryPaid',
                    'summaryUnpaid',
                    'summaryTotal',
                ))->render(),
            ]);
        }

        return view('admin.special-installments.show', compact(
            'specialInstallment',
            'rows',
            'filteredCount',
            'summaryPaid',
            'summaryUnpaid',
            'summaryTotal',
        ));
    }

    public function update(Request $request, SpecialInstallment $specialInstallment, SpecialInstallmentService $installments): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'paid' => ['required', 'boolean'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);

        /** @var User $admin */
        $admin = $request->user();
        $payment = $installments->setPaidStatus($specialInstallment, $user, (bool) $validated['paid'], $admin);
        $counts = $this->counts($installments, $specialInstallment);

        return response()->json([
            'ok' => true,
            'user_id' => $user->id,
            'is_paid' => $payment->isPaid(),
            'paid_at' => $payment->paid_at?->format('Y-m-d H:i'),
            'summary_html' => view('admin.special-installments.partials.summary', $counts)->render(),
        ]);
    }

    public function bulkUpdate(Request $request, SpecialInstallment $specialInstallment, SpecialInstallmentService $installments): JsonResponse
    {
        $validated = $request->validate([
            'paid' => ['required', 'boolean'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        /** @var User $admin */
        $admin = $request->user();

        $installments->setPaidStatusForUsers(
            $specialInstallment,
            $validated['user_ids'],
            (bool) $validated['paid'],
            $admin,
        );

        $counts = $this->counts($installments, $specialInstallment);

        return response()->json([
            'ok' => true,
            'summary_html' => view('admin.special-installments.partials.summary', $counts)->render(),
        ]);
    }

    /**
     * @return array{summaryPaid: int, summaryUnpaid: int, summaryTotal: int}
     */
    private function counts(SpecialInstallmentService $installments, SpecialInstallment $installment): array
    {
        $rows = $installments->rows($installment);
        $summaryPaid = $rows->where('is_paid', true)->count();
        $summaryTotal = $rows->count();

        return [
            'summaryPaid' => $summaryPaid,
            'summaryUnpaid' => $summaryTotal - $summaryPaid,
            'summaryTotal' => $summaryTotal,
        ];
    }
}

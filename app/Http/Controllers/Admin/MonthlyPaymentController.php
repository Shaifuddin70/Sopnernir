<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MonthlyPaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyPaymentController extends Controller
{
    public function index(Request $request, MonthlyPaymentService $payments): View|JsonResponse
    {
        $month = $this->resolveMonth($request);
        $search = trim((string) $request->query('search', ''));
        $searchTerm = $search !== '' ? $search : null;

        $allRows = $payments->rowsForMonth($month);

        $summaryPaid = $allRows->where('is_paid', true)->count();
        $summaryTotal = $allRows->count();
        $summaryUnpaid = $summaryTotal - $summaryPaid;

        $rows = $allRows;

        if ($searchTerm) {
            $needle = strtolower($searchTerm);
            $rows = $rows->filter(function (array $row) use ($needle): bool {
                return str_contains(strtolower($row['name']), $needle)
                    || str_contains(strtolower($row['email']), $needle)
                    || str_contains(strtolower((string) ($row['phone'] ?? '')), $needle);
            })->values();
        }

        $filteredCount = $rows->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin.monthly-payments.partials.table-fragment', compact(
                    'rows',
                    'month',
                    'filteredCount',
                    'summaryTotal',
                ))->render(),
                'summary_html' => view('admin.monthly-payments.partials.summary', compact(
                    'month',
                    'summaryPaid',
                    'summaryUnpaid',
                    'summaryTotal',
                ))->render(),
                'summary' => [
                    'paid' => $summaryPaid,
                    'unpaid' => $summaryUnpaid,
                    'total' => $summaryTotal,
                ],
            ]);
        }

        return view('admin.monthly-payments.index', compact(
            'rows',
            'month',
            'filteredCount',
            'summaryPaid',
            'summaryUnpaid',
            'summaryTotal',
        ));
    }

    public function update(Request $request, MonthlyPaymentService $payments): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'month' => ['required', 'date_format:Y-m'],
            'paid' => ['required', 'boolean'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $month = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();

        /** @var User $admin */
        $admin = $request->user();

        if ($request->ajax() || $request->wantsJson()) {
            $payment = $payments->setPaidStatus($user, $month, (bool) $validated['paid'], $admin);
            $allRows = $payments->rowsForMonth($month);
            $summaryPaid = $allRows->where('is_paid', true)->count();
            $summaryTotal = $allRows->count();
            $summaryUnpaid = $summaryTotal - $summaryPaid;

            return response()->json([
                'ok' => true,
                'user_id' => $user->id,
                'is_paid' => $payment->isPaid(),
                'paid_at' => $payment->paid_at?->format('Y-m-d H:i'),
                'summary' => [
                    'paid' => $summaryPaid,
                    'unpaid' => $summaryUnpaid,
                    'total' => $summaryTotal,
                ],
                'summary_html' => view('admin.monthly-payments.partials.summary', compact(
                    'month',
                    'summaryPaid',
                    'summaryUnpaid',
                    'summaryTotal',
                ))->render(),
            ]);
        }

        $payments->setPaidStatus($user, $month, (bool) $validated['paid'], $admin);

        return back()->with('status', __('Payment status updated.'));
    }

    public function bulkUpdate(Request $request, MonthlyPaymentService $payments): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'paid' => ['required', 'boolean'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();

        /** @var User $admin */
        $admin = $request->user();

        $count = $payments->setPaidStatusForUsers(
            $validated['user_ids'],
            $month,
            (bool) $validated['paid'],
            $admin,
        );

        $message = (bool) $validated['paid']
            ? trans_choice('Marked :count investor paid.|Marked :count investors paid.', $count, ['count' => $count])
            : trans_choice('Marked :count investor unpaid.|Marked :count investors unpaid.', $count, ['count' => $count]);

        if ($request->ajax() || $request->wantsJson()) {
            $allRows = $payments->rowsForMonth($month);
            $summaryPaid = $allRows->where('is_paid', true)->count();
            $summaryTotal = $allRows->count();
            $summaryUnpaid = $summaryTotal - $summaryPaid;

            return response()->json([
                'ok' => true,
                'message' => $message,
                'paid' => (bool) $validated['paid'],
                'summary' => [
                    'paid' => $summaryPaid,
                    'unpaid' => $summaryUnpaid,
                    'total' => $summaryTotal,
                ],
                'summary_html' => view('admin.monthly-payments.partials.summary', compact(
                    'month',
                    'summaryPaid',
                    'summaryUnpaid',
                    'summaryTotal',
                ))->render(),
            ]);
        }

        return back()->with('status', $message);
    }

    private function resolveMonth(Request $request): Carbon
    {
        $raw = $request->query('month');

        if (is_string($raw) && preg_match('/^\d{4}-\d{2}$/', $raw)) {
            return Carbon::createFromFormat('Y-m', $raw)->startOfMonth();
        }

        return Carbon::now()->startOfMonth();
    }
}

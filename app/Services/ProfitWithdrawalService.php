<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentProfitWithdrawal;
use App\Models\InvestmentProfitWithdrawalBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProfitWithdrawalService
{
    public function __construct(
        private InvestmentDailyProfitService $dailyProfit,
    ) {}

    /**
     * @return array{
     *     investment_id: int,
     *     title: string,
     *     through_date: string,
     *     profit_through_date: string,
     *     already_withdrawn: string,
     *     available: string,
     *     can_withdraw: bool,
     * }
     */
    public function preview(Investment $investment, Carbon $throughDate): array
    {
        $throughDate = $throughDate->copy()->startOfDay();
        $this->assertThroughDateAllowed($investment, $throughDate);

        $profitThrough = $this->profitThroughDate($investment, $throughDate);
        $alreadyWithdrawn = $this->totalWithdrawn($investment);
        $available = max(0.0, round($profitThrough - $alreadyWithdrawn, 2));

        return [
            'investment_id' => $investment->id,
            'title' => (string) $investment->title,
            'through_date' => $throughDate->toDateString(),
            'profit_through_date' => $this->decimal($profitThrough),
            'already_withdrawn' => $this->decimal($alreadyWithdrawn),
            'available' => $this->decimal($available),
            'can_withdraw' => $available > 0,
        ];
    }

    /**
     * @param  list<int>  $investmentIds
     * @return list<array{
     *     investment_id: int,
     *     title: string,
     *     through_date: string,
     *     profit_through_date: string,
     *     already_withdrawn: string,
     *     available: string,
     *     can_withdraw: bool,
     * }>
     */
    public function previewMany(array $investmentIds, Carbon $throughDate): array
    {
        $investments = Investment::query()
            ->whereIn('id', $investmentIds)
            ->with('participants')
            ->get()
            ->keyBy('id');

        $rows = [];
        foreach ($investmentIds as $id) {
            $investment = $investments->get($id);
            if ($investment === null) {
                continue;
            }
            $rows[] = $this->preview($investment, $throughDate);
        }

        return $rows;
    }

    public function withdraw(Investment $investment, Carbon $throughDate, User $admin, ?string $notes = null): InvestmentProfitWithdrawalBatch
    {
        return $this->withdrawMany([$investment->id], $throughDate, $admin, $notes);
    }

    /**
     * One batch entry per withdraw action (single or multi-pool).
     *
     * @param  list<int>  $investmentIds
     */
    public function withdrawMany(array $investmentIds, Carbon $throughDate, User $admin, ?string $notes = null): InvestmentProfitWithdrawalBatch
    {
        $throughDate = $throughDate->copy()->startOfDay();
        $notes = $notes !== null && trim($notes) !== '' ? trim($notes) : null;

        return DB::transaction(function () use ($investmentIds, $throughDate, $admin, $notes) {
            $items = [];

            foreach ($investmentIds as $id) {
                $investment = Investment::query()
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->with('participants')
                    ->first();

                if ($investment === null) {
                    continue;
                }

                $this->assertThroughDateAllowed($investment, $throughDate);

                $profitThrough = $this->profitThroughDate($investment, $throughDate);
                $alreadyWithdrawn = $this->totalWithdrawn($investment);
                $available = max(0.0, round($profitThrough - $alreadyWithdrawn, 2));

                if ($available <= 0) {
                    continue;
                }

                $items[] = [
                    'investment' => $investment,
                    'profit_through' => $profitThrough,
                    'available' => $available,
                    'lines' => $this->allocateToParticipants($investment->participants, $available),
                ];
            }

            if ($items === []) {
                throw new InvalidArgumentException(__('No withdrawable profit for the selected investments through the chosen date.'));
            }

            $total = round(array_sum(array_column($items, 'available')), 2);
            $withdrawnAt = now();

            $batch = InvestmentProfitWithdrawalBatch::query()->create([
                'through_date' => $throughDate->toDateString(),
                'amount' => $this->decimal($total),
                'pools_count' => count($items),
                'notes' => $notes,
                'withdrawn_by' => $admin->id,
                'withdrawn_at' => $withdrawnAt,
            ]);

            foreach ($items as $item) {
                /** @var Investment $investment */
                $investment = $item['investment'];

                $withdrawal = InvestmentProfitWithdrawal::query()->create([
                    'batch_id' => $batch->id,
                    'investment_id' => $investment->id,
                    'through_date' => $throughDate->toDateString(),
                    'amount' => $this->decimal($item['available']),
                    'profit_through_date' => $this->decimal($item['profit_through']),
                    'notes' => $notes,
                    'withdrawn_by' => $admin->id,
                    'withdrawn_at' => $withdrawnAt,
                ]);

                foreach ($item['lines'] as $line) {
                    $withdrawal->lines()->create([
                        'user_id' => $line['user_id'],
                        'contribution_amount' => $this->decimal($line['contribution']),
                        'amount' => $this->decimal($line['amount_cents'] / 100),
                    ]);
                }
            }

            return $batch->load(['withdrawals.investment', 'withdrawnBy']);
        });
    }

    public function totalWithdrawn(Investment $investment): float
    {
        return round((float) $investment->profitWithdrawals()->sum('amount'), 2);
    }

    public function availableThrough(Investment $investment, ?Carbon $asOf = null): float
    {
        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();
        $profit = $this->profitThroughDate($investment, $asOf);

        return max(0.0, round($profit - $this->totalWithdrawn($investment), 2));
    }

    private function profitThroughDate(Investment $investment, Carbon $throughDate): float
    {
        $pool = $this->dailyProfit->grossPoolProjection($investment, $throughDate);

        return $pool === null ? 0.0 : round((float) $pool['profit_til_today'], 2);
    }

    private function assertThroughDateAllowed(Investment $investment, Carbon $throughDate): void
    {
        $today = Carbon::today()->startOfDay();
        if ($throughDate->gt($today)) {
            throw new InvalidArgumentException(__('Withdrawal date cannot be in the future.'));
        }

        $start = $investment->planStartDay();
        if ($throughDate->lt($start)) {
            throw new InvalidArgumentException(__('Withdrawal date cannot be before the plan start (:date).', [
                'date' => $start->translatedFormat('j M Y'),
            ]));
        }
    }

    /**
     * @param  Collection<int, InvestmentParticipant>  $participants
     * @return list<array{user_id: int, contribution: float, amount_cents: int}>
     */
    private function allocateToParticipants(Collection $participants, float $amount): array
    {
        $profitCents = (int) round($amount * 100);
        $principal = (float) $participants->sum('contribution_amount');

        if ($participants->isEmpty() || $profitCents <= 0) {
            return [];
        }

        if ($principal <= 0 || $this->equalContributions($participants)) {
            $count = $participants->count();
            $share = (int) round($profitCents / $count);

            return $participants
                ->map(fn (InvestmentParticipant $p) => [
                    'user_id' => (int) $p->user_id,
                    'contribution' => (float) $p->contribution_amount,
                    'amount_cents' => $share,
                ])
                ->values()
                ->all();
        }

        $rows = [];
        $floors = [];
        $remainders = [];

        foreach ($participants as $participant) {
            $contrib = (float) $participant->contribution_amount;
            $exact = ($contrib / $principal) * $profitCents;
            $floor = (int) floor($exact);
            $rows[] = [
                'user_id' => (int) $participant->user_id,
                'contribution' => $contrib,
                'amount_cents' => $floor,
            ];
            $floors[] = $floor;
            $remainders[] = $exact - $floor;
        }

        $allocated = array_sum($floors);
        $leftover = $profitCents - $allocated;

        arsort($remainders);
        foreach (array_keys($remainders) as $index) {
            if ($leftover <= 0) {
                break;
            }
            $rows[$index]['amount_cents']++;
            $leftover--;
        }

        return $rows;
    }

    /**
     * @param  Collection<int, InvestmentParticipant>  $participants
     */
    private function equalContributions(Collection $participants): bool
    {
        if ($participants->count() <= 1) {
            return true;
        }

        return $participants
            ->map(fn (InvestmentParticipant $p) => number_format((float) $p->contribution_amount, 2, '.', ''))
            ->unique()
            ->count() === 1;
    }

    private function decimal(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}

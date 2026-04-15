<?php

namespace Database\Seeders;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use App\Services\InvestmentAccrualService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Creates 30 investor accounts and 10 active pools. Each pool has three distinct
 * participants, each with contribution 5000.00. Accruals are synced from six
 * months ago through the current calendar month.
 */
class DemoInvestorsAndInvestmentsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()
            ->where(function ($q): void {
                $q->where('is_admin', true)
                    ->orWhere('role', User::ROLE_ADMIN);
            })
            ->orderBy('id')
            ->first();

        if (! $admin) {
            $admin = User::factory()->admin()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        $investors = User::factory()->count(30)->create();

        $periodStart = Carbon::now()->subMonths(6)->startOfMonth();
        $through = Investment::accrualThroughInclusive();
        $accrual = app(InvestmentAccrualService::class);

        for ($i = 0; $i < 10; $i++) {
            $investment = Investment::create([
                'title' => 'Demo Pool '.($i + 1),
                'notes' => __('Seeded demo data.'),
                'default_monthly_rate_pct' => '1.5',
                'status' => Investment::STATUS_ACTIVE,
                'created_by' => $admin->id,
                'period_start' => $periodStart->toDateString(),
            ]);

            for ($j = 0; $j < 3; $j++) {
                InvestmentParticipant::create([
                    'investment_id' => $investment->id,
                    'user_id' => $investors[$i * 3 + $j]->id,
                    'contribution_amount' => '5000.00',
                ]);
            }

            $accrual->syncMonthsThrough($investment->fresh(), $through);
        }
    }
}

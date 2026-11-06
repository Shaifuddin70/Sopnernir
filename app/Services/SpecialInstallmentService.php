<?php

namespace App\Services;

use App\Models\SpecialInstallment;
use App\Models\SpecialInstallmentPayment;
use App\Models\User;
use Illuminate\Support\Collection;

class SpecialInstallmentService
{
    /**
     * @return Collection<int, array{
     *     user_id: int,
     *     name: string,
     *     email: string,
     *     phone: string|null,
     *     is_paid: bool,
     *     paid_at: string|null,
     * }>
     */
    public function rows(SpecialInstallment $installment): Collection
    {
        $payments = $installment->payments()->get()->keyBy('user_id');

        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($payments): array {
                /** @var SpecialInstallmentPayment|null $payment */
                $payment = $payments->get($user->id);

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_paid' => $payment?->isPaid() ?? false,
                    'paid_at' => $payment?->paid_at?->format('Y-m-d H:i'),
                ];
            });
    }

    public function setPaidStatus(SpecialInstallment $installment, User $user, bool $paid, User $recordedBy): SpecialInstallmentPayment
    {
        $payment = SpecialInstallmentPayment::query()
            ->where('special_installment_id', $installment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($payment === null) {
            $payment = new SpecialInstallmentPayment([
                'special_installment_id' => $installment->id,
                'user_id' => $user->id,
            ]);
        }

        $wasPaid = $payment->exists && $payment->isPaid();

        if ($paid) {
            $payment->paid_at = $payment->paid_at ?? now();
            $payment->recorded_by = $recordedBy->id;
        } else {
            $payment->paid_at = null;
            $payment->recorded_by = null;
        }

        $payment->save();

        if ($paid && ! $wasPaid) {
            app(InvestorNotificationService::class)->specialInstallmentRecorded($user, $installment);
        }

        return $payment;
    }

    /**
     * @param  list<int>  $userIds
     */
    public function setPaidStatusForUsers(SpecialInstallment $installment, array $userIds, bool $paid, User $recordedBy): int
    {
        $updated = 0;

        foreach (User::query()->whereIn('id', $userIds)->get() as $user) {
            $this->setPaidStatus($installment, $user, $paid, $recordedBy);
            $updated++;
        }

        return $updated;
    }
}

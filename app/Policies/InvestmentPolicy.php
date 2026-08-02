<?php

namespace App\Policies;

use App\Models\Investment;
use App\Models\User;

class InvestmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Investment $investment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $investment->is_active) {
            return false;
        }

        return $investment->participants()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Investment $investment): bool
    {
        return $user->isAdmin();
    }

    public function manageDocuments(User $user, Investment $investment): bool
    {
        return $user->isAdmin();
    }

    public function manageParticipants(User $user, Investment $investment): bool
    {
        return $user->isAdmin();
    }

    public function accrue(User $user, Investment $investment): bool
    {
        return $user->isAdmin();
    }

    public function withdrawProfits(User $user, Investment $investment): bool
    {
        return $user->isAdmin();
    }
}

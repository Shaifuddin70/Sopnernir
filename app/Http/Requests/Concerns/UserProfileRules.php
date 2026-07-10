<?php

namespace App\Http\Requests\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait UserProfileRules
{
    /**
     * @return array<string, mixed>
     */
    protected function userProfileRules(?int $userId, ?int $nomineeId = null): array
    {
        $emailRule = Rule::unique(User::class, 'email');
        $nidRule = Rule::unique(User::class, 'nid_number');
        if ($userId !== null) {
            $emailRule = $emailRule->ignore($userId);
            $nidRule = $nidRule->ignore($userId);
        }

        $nomineeNidRule = Rule::unique('nominees', 'nid_number');
        if ($nomineeId !== null) {
            $nomineeNidRule = $nomineeNidRule->ignore($nomineeId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', $emailRule],
            'phone' => ['required', 'string', 'max:32'],
            'nid_number' => ['required', 'string', 'max:64', $nidRule],
            'address' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'nominee.name' => ['required', 'string', 'max:255'],
            'nominee.email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'nominee.phone' => ['required', 'string', 'max:32'],
            'nominee.nid_number' => ['required', 'string', 'max:64', $nomineeNidRule],
            'nominee.address' => ['required', 'string', 'max:2000'],
            'nominee.image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}

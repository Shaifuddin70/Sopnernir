<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\UserProfileRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AdminStoreUserRequest extends FormRequest
{
    use UserProfileRules;

    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_admin' => $this->boolean('is_admin'),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->userProfileRules(null), [
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_admin' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }
}

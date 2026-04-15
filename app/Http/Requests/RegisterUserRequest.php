<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UserProfileRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    use UserProfileRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->userProfileRules(null),
            [
                'password' => ['required', 'confirmed', Password::defaults()],
            ]
        );
    }
}

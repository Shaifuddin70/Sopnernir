<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UserProfileRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use UserProfileRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->userProfileRules($this->user()->id);
    }
}

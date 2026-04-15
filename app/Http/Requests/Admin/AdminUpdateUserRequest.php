<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\UserProfileRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateUserRequest extends FormRequest
{
    use UserProfileRules;

    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'is_admin' => $this->boolean('is_admin'),
        ];
        if ($this->exists('is_active')) {
            $merge['is_active'] = $this->boolean('is_active');
        }
        $this->merge($merge);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $target = $this->route('user');
        if (! $target instanceof \App\Models\User) {
            return [];
        }

        $rules = array_merge($this->userProfileRules($target->id), [
            'is_admin' => ['boolean'],
        ]);
        if ($this->exists('is_active')) {
            $rules['is_active'] = ['boolean'];
        }

        return $rules;
    }
}

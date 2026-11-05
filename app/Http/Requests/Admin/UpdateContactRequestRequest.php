<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContactRequestStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ContactRequestStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

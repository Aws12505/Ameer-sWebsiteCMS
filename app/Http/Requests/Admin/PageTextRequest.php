<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The Services and Projects page texts.
 */
class PageTextRequest extends FormRequest
{
    use ContentValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => $this->textRules(40),
            'intro' => $this->textRules(160),
            'seo.title' => $this->textRules(60, required: false),
            'seo.description' => $this->textRules(160, required: false),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['seo.title' => 'search title', 'seo.description' => 'search description'];
    }
}

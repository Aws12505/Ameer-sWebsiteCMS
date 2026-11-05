<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContactPageRequest extends FormRequest
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
            'intro' => $this->textRules(120),
            'form.heading' => $this->textRules(40),
            'form.intro' => $this->textRules(120),
            'success.heading' => $this->textRules(60),
            'success.text' => $this->textRules(200),
            'direct.heading' => $this->textRules(40),
            'next.heading' => $this->textRules(40),
            ...$this->iconItemRules('next.steps', 3, 32, 70),
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
        return [
            '*.heading' => 'heading',
            '*.intro' => 'intro',
            'success.text' => 'thank-you text',
            'next.steps.*.title' => 'title',
            'next.steps.*.text' => 'text',
            'next.steps.*.icon' => 'icon',
            'seo.title' => 'search title',
            'seo.description' => 'search description',
        ];
    }
}

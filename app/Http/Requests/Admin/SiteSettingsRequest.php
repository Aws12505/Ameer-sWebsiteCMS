<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use App\Rules\StoredMedia;
use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SiteSettingsRequest extends FormRequest
{
    use ContentValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * Only an Admin may change who receives the contact requests.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->textRules(60),
            'description' => $this->textRules(160),
            'favicon' => ['required', 'array'],
            'favicon.path' => ['required', 'string', new StoredMedia],
            'serviceArea' => $this->textRules(40),
            'phone.display' => [...$this->textRules(20), function (string $attribute, mixed $value, Closure $fail): void {
                if (PhoneNumber::digitCount((string) $value) < 10) {
                    $fail('Please enter the full phone number with the area code.');
                }
            }],
            'email' => ['required', 'string', 'email', 'max:254'],
            'cta.label' => $this->textRules(30),
            'footerBlurb' => $this->textRules(110),
            'ctaBand.heading' => $this->textRules(50),
            'ctaBand.text' => $this->textRules(100),
            ...$this->imageRules('defaultShareImage', required: false),
            ...($this->user()->isAdmin() ? [
                'notificationEmails' => ['present', 'array', 'max:5'],
                'notificationEmails.*' => ['required', 'string', 'email', 'max:254', 'distinct'],
            ] : [
                'notificationEmails' => ['prohibited'],
            ]),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->contentMessages(['defaultShareImage']),
            'favicon.required' => 'Please upload the browser tab icon.',
            'favicon.path.required' => 'Please upload the browser tab icon.',
            'notificationEmails.max' => 'You can add up to 5 email addresses.',
            'notificationEmails.prohibited' => 'Only an Admin can change who receives contact requests.',
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
            'name' => 'site name',
            'description' => 'site description',
            'serviceArea' => 'service area',
            'phone.display' => 'phone number',
            'cta.label' => 'main button label',
            'footerBlurb' => 'footer line',
            'ctaBand.heading' => 'CTA band heading',
            'ctaBand.text' => 'CTA band line',
            'notificationEmails.*' => 'email address',
        ];
    }
}

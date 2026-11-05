<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
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
            'slug' => $this->slugRules(Service::class, $this->route('service')),
            'title' => $this->textRules(40),
            'description' => $this->textRules(200),
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'distinct', Rule::exists(ServiceCategory::class, 'id')->withoutTrashed()],
            'includes' => ['required', 'array', 'min:2', 'max:4'],
            'includes.*' => $this->textRules(60),
            ...$this->imageRules('image'),
            'published' => ['boolean'],
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
            ...$this->contentMessages(['image']),
            'category_ids.required' => 'Please choose at least one core service.',
            'category_ids.min' => 'Please choose at least one core service.',
            'includes.required' => 'Please add 2 to 4 "What\'s included" items.',
            'includes.min' => 'Please add at least 2 "What\'s included" items.',
            'includes.max' => 'You can add up to 4 "What\'s included" items.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['slug' => 'key', 'includes.*' => 'item'];
    }
}

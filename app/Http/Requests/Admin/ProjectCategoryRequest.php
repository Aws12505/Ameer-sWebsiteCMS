<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use App\Models\ProjectCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProjectCategoryRequest extends FormRequest
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
            'slug' => $this->slugRules(ProjectCategory::class, $this->route('projectCategory')),
            'label' => $this->textRules(20),
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
        return $this->contentMessages();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['slug' => 'key'];
    }
}

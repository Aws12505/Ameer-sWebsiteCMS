<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use App\Models\Review;
use App\Rules\StoredMedia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
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
            'slug' => $this->slugRules(Review::class, $this->route('review')),
            'name' => $this->textRules(40),
            'project' => $this->textRules(60),
            'rating' => ['required', 'integer', 'between:1,5'],
            'quote' => $this->textRules(200),
            'photo' => ['nullable', 'array'],
            'photo.path' => ['required_with:photo', 'string', new StoredMedia],
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
        return ['slug' => 'key', 'project' => 'project line'];
    }
}

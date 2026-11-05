<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use App\Models\ServiceCategory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ServiceCategoryRequest extends FormRequest
{
    use ContentValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ServiceCategory|null $category */
        $category = $this->route('serviceCategory');
        $isExternal = filled($this->input('external_url'));

        return [
            'slug' => $this->slugRules(ServiceCategory::class, $category),
            'title' => $this->textRules(40),
            'filter_label' => $this->textRules(20),
            'summary' => $this->textRules(160),
            ...$this->imageRules('image'),
            ...($isExternal
                ? ['body' => ['nullable', 'array', 'max:3'], 'body.*' => $this->textRules(400)]
                : $this->paragraphRules('body', 2, 3, 400)),
            ...$this->galleryRules('gallery', 0, 8),
            'external_url' => ['nullable', 'url:https', 'max:200'],
            'show_on_home' => ['boolean', function (string $attribute, mixed $value, Closure $fail) use ($category): void {
                $othersOnHome = ServiceCategory::query()
                    ->where('show_on_home', true)
                    ->when($category, fn ($query) => $query->whereKeyNot($category->id))
                    ->count();

                if ($value && $othersOnHome >= ServiceCategory::MaxOnHome) {
                    $fail('Only '.ServiceCategory::MaxOnHome.' core services can show on the home page. Switch one off first.');
                }
            }],
            'is_pinned' => ['boolean'],
            'seo_title' => $this->textRules(60, required: false),
            'seo_description' => $this->textRules(160, required: false),
            ...$this->imageRules('seo_image', required: false),
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
            ...$this->contentMessages(['image', 'seo_image', 'gallery.*']),
            'body.required' => 'Please write 2 or 3 paragraphs for the service page.',
            'body.min' => 'Please write at least 2 paragraphs for the service page.',
            'body.max' => 'The service page can have up to 3 paragraphs.',
            'external_url.url' => 'Please enter a full web address starting with https://',
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
            'slug' => 'key',
            'filter_label' => 'filter chip name',
            'body.*' => 'paragraph',
            'external_url' => 'external link',
            'seo_title' => 'search title',
            'seo_description' => 'search description',
        ];
    }
}

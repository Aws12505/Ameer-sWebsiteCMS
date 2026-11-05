<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use App\Models\Project;
use App\Models\ProjectCategory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    use ContentValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Project|null $project */
        $project = $this->route('project');

        return [
            'slug' => $this->slugRules(Project::class, $project),
            'title' => $this->textRules(40),
            'location' => [...$this->textRules(40), 'regex:/^[^,]+, [A-Z]{2}$/'],
            'description' => $this->textRules(150),
            'main_category_id' => ['required', 'integer', Rule::exists(ProjectCategory::class, 'id')->withoutTrashed()],
            'extra_category_ids' => ['array'],
            'extra_category_ids.*' => ['integer', 'distinct', Rule::exists(ProjectCategory::class, 'id')->withoutTrashed()],
            'featured' => ['boolean', function (string $attribute, mixed $value, Closure $fail) use ($project): void {
                $othersFeatured = Project::query()
                    ->where('featured', true)
                    ->when($project, fn ($query) => $query->whereKeyNot($project->id))
                    ->count();

                if ($value && $othersFeatured >= Project::MaxFeatured) {
                    $fail('Only '.Project::MaxFeatured.' projects can be featured on the home page. Switch one off first.');
                }
            }],
            ...$this->imageRules('cover'),
            ...$this->galleryRules('gallery', 0, 19),
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
            ...$this->contentMessages(['cover', 'gallery.*']),
            'location.regex' => 'Please write the location as "City, ST" (for example Columbus, OH).',
            'main_category_id.required' => 'Please choose the main category.',
            'gallery.max' => 'A project can have up to 20 photos, including the cover.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['slug' => 'key', 'main_category_id' => 'main category'];
    }
}

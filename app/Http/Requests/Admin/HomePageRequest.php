<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class HomePageRequest extends FormRequest
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
            'hero.headline' => ['required', 'array', 'min:1', 'max:2'],
            'hero.headline.0' => $this->textRules(40),
            'hero.headline.1' => $this->textRules(40, required: false),
            'hero.subline' => $this->textRules(120),
            'hero.secondaryButtonLabel' => $this->textRules(30),

            'why.heading' => $this->textRules(40),
            'why.intro' => $this->textRules(70),
            ...$this->iconItemRules('why.points', 4, 32, 90),

            'about.label' => $this->textRules(24),
            'about.heading' => $this->textRules(70),
            ...$this->paragraphRules('about.paragraphs', 2, 2, 300),
            ...$this->imageRules('about.image'),
            'about.buttonLabel' => $this->textRules(30),
            ...$this->iconItemRules('about.pillars', 3, 16, 70),

            'services.label' => $this->textRules(24),
            'services.heading' => $this->textRules(60),
            'services.intro' => $this->textRules(120),
            'services.buttonLabel' => $this->textRules(30),

            'projects.label' => $this->textRules(24),
            'projects.heading' => $this->textRules(40),
            'projects.intro' => $this->textRules(180),
            'projects.buttonLabel' => $this->textRules(30),

            'process.label' => $this->textRules(24),
            'process.heading' => $this->textRules(50),
            'process.intro' => $this->textRules(140),
            ...$this->iconItemRules('process.steps', 4, 32, 100),

            'reviews.label' => $this->textRules(24),
            'reviews.heading' => $this->textRules(50),

            'seo.title' => $this->textRules(60, required: false),
            'seo.description' => $this->textRules(160, required: false),
            ...$this->imageRules('seo.image', required: false),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->contentMessages(['about.image', 'seo.image']);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'hero.headline.0' => 'headline line 1',
            'hero.headline.1' => 'headline line 2',
            'hero.subline' => 'short line',
            'hero.secondaryButtonLabel' => 'second button label',
            '*.label' => 'label',
            '*.heading' => 'heading',
            '*.intro' => 'intro',
            '*.buttonLabel' => 'button label',
            '*.*.*.title' => 'title',
            '*.*.*.text' => 'text',
            '*.*.*.icon' => 'icon',
            'about.paragraphs.*' => 'paragraph',
            'seo.title' => 'search title',
            'seo.description' => 'search description',
        ];
    }
}

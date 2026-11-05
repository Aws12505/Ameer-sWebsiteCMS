<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ContentValidationRules;
use App\Enums\HeroMode;
use App\Rules\StoredMedia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HeroSettingsRequest extends FormRequest
{
    use ContentValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * A video needs a video file and a poster, a slider needs at least
     * two photos and a still image needs one.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mode = HeroMode::tryFrom((string) $this->input('heroMode'));

        return [
            'showReviews' => ['required', 'boolean'],
            'heroMode' => ['required', Rule::enum(HeroMode::class)],
            ...$this->galleryRules('heroSlides', match ($mode) {
                HeroMode::Slider => 2,
                HeroMode::Image => 1,
                default => 0,
            }, 8),
            'heroVideo' => [Rule::requiredIf($mode === HeroMode::Video), 'nullable', 'array'],
            'heroVideo.path' => ['required_with:heroVideo', 'string', new StoredMedia],
            'heroVideo.poster' => ['required_with:heroVideo', 'string', new StoredMedia],
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
            ...$this->contentMessages(['heroSlides.*']),
            'heroSlides.required' => 'Please add at least one photo.',
            'heroSlides.min' => 'The slider needs at least 2 photos.',
            'heroSlides.max' => 'You can add up to 8 photos.',
            'heroVideo.required' => 'Please upload a video and a poster picture.',
            'heroVideo.path.required_with' => 'Please upload the video file.',
            'heroVideo.poster.required_with' => 'Please upload the poster picture shown before the video plays.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $uploads = config('cms.uploads');

        return [
            'kind' => ['required', Rule::in(['image', 'video', 'favicon'])],
            'file' => match ($this->input('kind')) {
                'video' => ['required', 'file', 'mimes:'.implode(',', $uploads['video_mimes']), 'max:'.$uploads['video_max_kb']],
                'favicon' => ['required', 'file', 'mimes:png', 'max:'.$uploads['image_max_kb'], 'dimensions:ratio=1/1'],
                default => ['required', 'file', 'mimes:'.implode(',', $uploads['image_mimes']), 'max:'.$uploads['image_max_kb']],
            },
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
            'file.required' => 'Please choose a file.',
            'file.mimes' => match ($this->input('kind')) {
                'video' => 'Please upload an MP4 video.',
                'favicon' => 'Please upload a PNG file.',
                default => 'Please upload a JPG, PNG or WebP photo.',
            },
            'file.max' => 'This file is too large. The limit is 10 MB.',
            'file.dimensions' => 'The tab icon must be square (for example 512 × 512).',
            'file.uploaded' => 'The upload failed. The file may be too large.',
        ];
    }
}

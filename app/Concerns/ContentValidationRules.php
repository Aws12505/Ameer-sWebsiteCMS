<?php

namespace App\Concerns;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Rules\SingleLine;
use App\Rules\StoredMedia;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Unique;

/**
 * Shared validation for the content screens (requirements section 3).
 */
trait ContentValidationRules
{
    /**
     * Plain text on one line with a character limit.
     *
     * @return array<int, ValidationRule|string>
     */
    protected function textRules(int $max, bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'max:'.$max, new SingleLine];
    }

    /**
     * An image with a required short description (alt text).
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function imageRules(string $field, bool $required = true): array
    {
        return [
            $field => [$required ? 'required' : 'nullable', 'array'],
            $field.'.path' => ['required_with:'.$field, 'string', new StoredMedia],
            $field.'.alt' => ['required_with:'.$field, 'string', 'max:125', new SingleLine],
        ];
    }

    /**
     * An ordered list of images, each with alt text.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function galleryRules(string $field, int $min, int $max): array
    {
        return [
            $field => [$min > 0 ? 'required' : 'nullable', 'array', 'min:'.$min, 'max:'.$max],
            $field.'.*.path' => ['required', 'string', new StoredMedia],
            $field.'.*.alt' => ['required', 'string', 'max:125', new SingleLine],
        ];
    }

    /**
     * A list of plain paragraphs.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function paragraphRules(string $field, int $min, int $max, int $maxLength, bool $required = true): array
    {
        return [
            $field => [$required ? 'required' : 'nullable', 'array', 'min:'.$min, 'max:'.$max],
            $field.'.*' => $this->textRules($maxLength),
        ];
    }

    /**
     * A fixed-size list of `{ icon, title, text }` items.
     *
     * @return array<string, array<int, ValidationRule|string|In>>
     */
    protected function iconItemRules(string $field, int $count, int $titleMax, int $textMax): array
    {
        return [
            $field => ['required', 'array', 'size:'.$count],
            $field.'.*.icon' => ['required', Rule::in(config('cms.icons'))],
            $field.'.*.title' => $this->textRules($titleMax),
            $field.'.*.text' => $this->textRules($textMax),
        ];
    }

    /**
     * A URL key: lowercase letters, numbers and hyphens, unique, and locked
     * once the record has been published.
     *
     * @param  class-string<Model>  $modelClass
     * @return array<int, Closure|ValidationRule|string|Unique>
     */
    protected function slugRules(string $modelClass, ServiceCategory|Service|ProjectCategory|Project|Review|null $record = null): array
    {
        return [
            'required',
            'string',
            'max:40',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::unique($modelClass, 'slug')->ignore($record?->getKey()),
            function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                if ($record !== null && $record->isKeyLocked() && $value !== $record->slug) {
                    $fail('This key is locked because the item has been published. Changing it would break links on the live site.');
                }
            },
        ];
    }

    /**
     * Plain-English messages for the image and key fields.
     *
     * @param  list<string>  $imageFields
     * @return array<string, string>
     */
    protected function contentMessages(array $imageFields = []): array
    {
        $messages = [
            'slug.regex' => 'The key can only use lowercase letters, numbers and hyphens (for example new-construction).',
            'slug.unique' => 'This key is already used by another item (check the trash too).',
        ];

        foreach ($imageFields as $field) {
            $messages[$field.'.required'] = 'Please upload a photo.';
            $messages[$field.'.path.required_with'] = 'Please upload a photo.';
            $messages[$field.'.path.required'] = 'Please upload a photo.';
            $messages[$field.'.alt.required_with'] = 'Please add a short description of the photo.';
            $messages[$field.'.alt.required'] = 'Please add a short description of the photo.';
            $messages[$field.'.alt.max'] = 'Please keep the photo description under 125 characters.';
        }

        return $messages;
    }
}

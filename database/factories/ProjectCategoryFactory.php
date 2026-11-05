<?php

namespace Database\Factories;

use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectCategory>
 */
class ProjectCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = Str::title(fake()->unique()->word());

        return [
            'slug' => Str::slug($label),
            'label' => $label,
            'published' => true,
        ];
    }

    /**
     * Indicate that the category is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }
}

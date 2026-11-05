<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->word().' '.fake()->word());

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'location' => 'Columbus, OH',
            'description' => fake()->sentence(),
            'main_category_id' => ProjectCategory::factory(),
            'featured' => false,
            'cover_path' => 'seed/cover.jpg',
            'cover_alt' => 'The finished project',
            'gallery' => [],
            'published' => true,
        ];
    }

    /**
     * Indicate that the project is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }

    /**
     * Indicate that the project is featured on the home page.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['featured' => true]);
    }
}

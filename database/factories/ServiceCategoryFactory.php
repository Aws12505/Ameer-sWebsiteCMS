<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
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
            'filter_label' => Str::limit($title, 20, ''),
            'summary' => fake()->sentence(),
            'image_path' => 'seed/core-service.jpg',
            'image_alt' => 'A finished project',
            'body' => [fake()->sentence(), fake()->sentence()],
            'gallery' => [],
            'show_on_home' => false,
            'is_pinned' => false,
            'published' => true,
        ];
    }

    /**
     * Indicate that the core service is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }

    /**
     * Indicate that another company handles the service.
     */
    public function external(string $url = 'https://pnehomes.com/'): static
    {
        return $this->state(fn (array $attributes) => ['external_url' => $url, 'body' => []]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
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
            'description' => fake()->sentence(),
            'includes' => ['Planning', 'Building', 'Clean-up'],
            'image_path' => 'seed/service.jpg',
            'image_alt' => 'A finished room',
            'published' => true,
        ];
    }

    /**
     * Indicate that the service is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }
}

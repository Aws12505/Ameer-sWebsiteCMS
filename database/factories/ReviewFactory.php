<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'project' => 'Kitchen remodel, Columbus, OH',
            'rating' => 5,
            'quote' => fake()->sentence(),
            'photo_path' => null,
            'published' => true,
        ];
    }

    /**
     * Indicate that the review is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }
}

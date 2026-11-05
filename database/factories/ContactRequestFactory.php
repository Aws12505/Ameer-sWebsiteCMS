<?php

namespace Database\Factories;

use App\Enums\ContactRequestStatus;
use App\Models\ContactRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactRequest>
 */
class ContactRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '(614) 555-0100',
            'email' => fake()->safeEmail(),
            'project_type' => 'Residential Remodeling',
            'address' => '43015',
            'message' => fake()->paragraph(),
            'status' => ContactRequestStatus::New,
        ];
    }
}

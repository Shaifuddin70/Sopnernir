<?php

namespace Database\Factories;

use App\Models\Nominee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nominee>
 */
class NomineeFactory extends Factory
{
    protected $model = Nominee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('01#########'),
            'nid_number' => fake()->unique()->uuid(),
            'address' => fake()->address(),
            'image' => null,
        ];
    }
}

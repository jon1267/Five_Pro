<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'title' => fake()->title(),
            'description' => fake()->text(),
            'edition' => fake()->numerify('#'),
            'publisher' => fake()->company(),
            'year' => fake()->year(),
            'format' => fake()->randomElement(['paperback', 'hardcover']),
            'pages' => fake()->numerify(),
            'country' => fake()->country(),
            'isbn' => fake()->isbn13(),
        ];
    }

}

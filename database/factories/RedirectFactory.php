<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RedirectLog;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code' => fake()->uuid,
            'status' => fake()->randomElement(['ativo', 'inativo']),
            'url_destiny' => fake()->url,
            'last_access' => fake()->dateTimeThisYear(),
            'deleted_at' => fake()->randomElement([fake()->dateTimeThisYear(), null, null, null]),
        ];
    }
}

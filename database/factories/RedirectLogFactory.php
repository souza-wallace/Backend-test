<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Redirect;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RedirectLog>
 */
class RedirectLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $redirectId = Redirect::inRandomOrder()->value('id');

        return [
            'redirect_id' => $redirectId,
            'ip_address' => fake()->ipv4,
            'user_agent' => fake()->userAgent,
            'referer' => fake()->url,
            // 'query_params' => fake()->randomElements(['param1' => 'value1', 'param2' => 'value2'], $count = 2, $allowDuplicates = false), // Gera dados JSON simulados
            'access_time' => fake()->dateTimeThisYear(),
            'created_at' => fake()->dateTimeThisYear(),
            'updated_at' => fake()->dateTimeThisYear(),
        ];
    }
}

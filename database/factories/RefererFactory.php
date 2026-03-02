<?php

namespace Database\Factories;

use App\Models\Referer;
use App\Models\RefererType;
use App\Models\Title;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Referer>
 */
class RefererFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referer_type_id' => RefererType::inRandomOrder()->first()?->id ?? RefererType::factory(),
            'title_id' => Title::inRandomOrder()->first()?->id ?? Title::factory(),
            'name' => $this->faker->name(),
            'mobile_no' => $this->faker->phoneNumber(),
            'email_id' => $this->faker->unique()->safeEmail(),
            'place' => $this->faker->city(),
            'hospital_name' => $this->faker->company() . ' Hospital',
            'hospital_id' => $this->faker->bothify('HOSP-####'),
            'status' => 'active',
            'added_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Title;
use App\Models\BloodGroup;
use App\Models\Gender;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => $this->faker->unique()->bothify('PAT-#####'),
            'mrn_id' => $this->faker->unique()->bothify('MRN-#####'),
            'title_fk_id' => Title::inRandomOrder()->first()?->id ?? Title::factory(),
            'name' => $this->faker->name(),
            'father_name' => $this->faker->name('male'),
            'email_id' => $this->faker->unique()->safeEmail(),
            'dob' => $this->faker->date('Y-m-d', '-18 years'),
            'mobile_no' => $this->faker->phoneNumber(),
            'whatsapp_no' => $this->faker->phoneNumber(),
            'blood_group_fk_id' => BloodGroup::inRandomOrder()->first()?->id ?? BloodGroup::factory(),
            'gender_fk_id' => Gender::inRandomOrder()->first()?->id ?? Gender::factory(),
            'place' => $this->faker->city(),
            'remarks' => $this->faker->sentence(),
            'status' => 'active',
            'added_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
        ];
    }
}

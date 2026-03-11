<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Referer;
use App\Models\Patient;
use App\Models\RefererType;
use App\Models\Title;
use App\Models\Gender;

use App\Models\User;

class RefererAndPatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure master data exists
        if (RefererType::count() === 0) {
            $types = ['Doctor', 'Hospital', 'Internal', 'External'];
            foreach ($types as $type) {
                RefererType::create(['name' => $type, 'status' => 'active', 'added_by' => User::first()?->id]);
            }
        }

        // Call other seeders if they haven't been run
        if (Title::count() === 0) {
            $this->call(TitleSeeder::class);
        }
        if (Gender::count() === 0) {
            $this->call(GenderSeeder::class);
        }


        // Create 20 Referers
        Referer::factory()->count(20)->create();

        // Create 20 Patients
        Patient::factory()->count(20)->create();
    }
}

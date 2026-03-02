<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Define the mapping of scan types to body parts with amounts
        $mapping = [
            'CT' => [
                'Brain' => 2500,
                'Chest' => 3000,
                'Abdomen' => 3500,
                'Pelvis' => 3000,
                'Spine' => 2800
            ],
            'MRI' => [
                'Brain' => 5000,
                'Spine' => 5500,
                'Knee' => 4500,
                'Shoulder' => 4500,
                'Abdomen' => 6000
            ],
            'USG' => [
                'Whole Abdomen' => 1000,
                'Pelvis' => 800,
                'Obstetric' => 1200,
                'Small Parts' => 1500,
                'Doppler' => 2000
            ],
            'DOR' => [
                'Full Mouth' => 1500,
                'TMJ' => 1200,
                'Maxilla' => 1000,
                'Mandible' => 1000
            ],
            'XRAY' => [
                'Chest' => 500,
                'Spine' => 600,
                'Extremities' => 400,
                'Skull' => 500,
                'Abdomen' => 600
            ],
        ];

        // Clean up old scan types that aren't in the new mapping
        DB::table('scan_types')->whereNotIn('name', array_keys($mapping))->delete();

        // Insert scan types and their associated scans with amounts
        foreach ($mapping as $typeName => $scans) {
            DB::table('scan_types')->updateOrInsert(
                ['name' => $typeName],
                ['created_at' => $now, 'updated_at' => $now, 'status' => 'active']
            );

            $typeId = DB::table('scan_types')->where('name', $typeName)->value('id');

            foreach ($scans as $scanName => $amount) {
                DB::table('scans')->updateOrInsert(
                    ['name' => $scanName, 'scan_type_id' => $typeId],
                    [
                        'amount' => $amount,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'status' => 'active'
                    ]
                );
            }
        }
    }
}

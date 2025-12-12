<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

    
class WhatsAppTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('whatsapp_templates')->insert([
            'event_name' => 'SCAN_REPORT_READY',
            'template_content' => 'Hello {$patientName}, your report (ID: {$reportId}) dated {$reportDate} is available.',
            'whatsapp_content' => 'Hello {$patientName}, your report (ID: {$reportId}) dated {$reportDate} is available.',
            'parameters' => 'patient_name,report_id',
            'allow_to_send' => 1,
            'status' => 1,
            'sender_id' => 'CLINIC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

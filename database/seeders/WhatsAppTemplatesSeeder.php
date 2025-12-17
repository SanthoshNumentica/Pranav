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
            'template_content' => "Dear Dr. {doctorName},\nDigital images of your patient {patientName}'s scan (ID: {reportId}) done on {reportDate} are now available via Nandico PACS.\n\nClick on link to view the scan here:\n{shareLink}",
            'whatsapp_content' => "Dear Dr. {doctorName},\nDigital images of your patient {patientName}'s scan (ID: {reportId}) done on {reportDate} are now available via Nandico PACS.\n\nClick on link to view the scan here:\n{shareLink}",
            'parameters' => 'doctorName,patient_name,report_id,reportDate,shareLink',
            'allow_to_send' => 1,
            'status' => 1,
            'sender_id' => 'CLINIC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

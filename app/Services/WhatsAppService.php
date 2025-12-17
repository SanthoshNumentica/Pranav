<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\WhatsappTemplate;

class WhatsAppService
{
    public function __construct() {}

    /**
     * Send WhatsApp message
     */
    public function sendTemplateMessage($event, $params = [], $toNumber = null)
    {
        // 1. Fetch template from whatsapp_templates table
        $template = WhatsappTemplate::where('event_name', $event)
            ->where('allow_to_send', 1)
            ->first();

        if (! $template) {
            return ['status' => false, 'message' => 'Template not found'];
        }

        // 2. Replace parameters in template
        $message = $this->replaceParameters($template->template_content, $params);

        // 3. Normalize number
        $mobileNo = $toNumber ?: $params['mobile_no'] ?? null;
        if (!$mobileNo) return ['status' => false, 'message' => 'Mobile number missing'];
        $mobileNo = preg_replace('/\D/', '', $mobileNo);
        if (!str_starts_with($mobileNo, '91')) {
            $mobileNo = '91' . $mobileNo;
        }

        // 4. Send to WhatsApp API
        $result = $this->sendWhatsAppApi($mobileNo, $message);

        return $result;
    }

    /**
     * Replace placeholders in template
     */
    private function replaceParameters($template, $params)
    {
        foreach ($params as $key => $value) {
            $template = str_replace('{$' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Call WhatsApp API and log
     */
    private function sendWhatsAppApi($mobileNo, $message)
    {
        $url = config('services.whatsapp.api_url');
        $appkey = config('services.whatsapp.appkey');
        $authkey = config('services.whatsapp.authkey');

        try {
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                'appkey' => $appkey,
            ])->post($url, [
                'appkey' => $appkey,
                'authkey' => $authkey,
                'to' => $mobileNo,
                'message' => $message,
            ]);

            $body = trim($response->body());
            $success = str_contains(strtolower($body), 'true');

            // 5. Log into whatsapp_log table
            DB::table('whatsapp_log')->insert([
                'from' => config('services.whatsapp.sender_number', 'DefaultSender'),
                'to' => $mobileNo,
                'message_type' => 'Plain Text',
                'message' => $message,
                'status' => $success ? 'Sent' : 'Failed',
                'date' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['status' => $success, 'response' => $body];
        } catch (\Throwable $e) {
            DB::table('whatsapp_log')->insert([
                'from' => config('services.whatsapp.sender_number', 'DefaultSender'),
                'to' => $mobileNo,
                'message_type' => 'Plain Text',
                'message' => $message,
                'status' => 'Failed',
                'date' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['status' => false, 'response' => $e->getMessage()];
        }
    }
}

<?php
// cspell:ignore appkey authkey

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappLog;

class WhatsAppService
{

    public function __construct()
    {
    }

    /**
     * Send a WhatsApp message or push it into a queue.
     */
    public function send(array $data, bool $isSend = true, $templateId = null, bool $skipQueue = false): array
    {
        // case 1: Fresh send (no ID)
        if (empty($data['id'])) {
            $template = WhatsappTemplate::find($templateId);
            if (!$template) {
                return ['status' => false, 'message' => 'Template not found'];
            }

            $message = $this->replaceParameters($data, $template->template_content ?? '');
            if (!$isSend) {
                return ['status' => false, 'message' => 'WhatsApp sending skipped'];
            }

            // Normalize mobile number
            $mobileNo = preg_replace('/\D/', '', $data['mobile_no']);
            if (!str_starts_with($mobileNo, '91')) {
                $mobileNo = '91' . $mobileNo;
            }
            // Convert escaped newlines to actual line breaks
            $message = str_replace('\n', "\n", $message);

            // Create notification record
            $whatsappLog = new WhatsappLog();
            $whatsappLog->fill([
                'message' => $message,
                'message_type' => 'whatsapp',
                'status' => 'not_sent',
                'created_by' => auth()->check() ? auth()->user()->id : null,
                'recipient_mobile_no' => $mobileNo,
            ]);
            $whatsappLog->save();
        }
        // case 2: Resend (existing ID) 
        else {
            $whatsappLog = WhatsappLog::find($data['id']);
            if (!$whatsappLog) {
                return ['status' => false, 'message' => 'Whatsapp Log Data not found'];
            }

            $mobileNo = $whatsappLog->recipient_mobile_no;
            $message = $whatsappLog->message;
        }

        // Send whatsapp message
        $result = $this->sendWhatsAppMessage($mobileNo, $message);
        $decoded = json_decode($result['response'] ?? '', true);

        $senderNumber = $decoded['data']['from'] ?? '';

        if (isset($decoded['data']['status_code']) && (int) $decoded['data']['status_code'] === 200) {
            $whatsappLog->status = 'sent';
        } else {
            $whatsappLog->status = 'failed';
        }

        // update with sender number && API response
        $whatsappLog->sender_mobile_no = $senderNumber;
        $whatsappLog->response = $result['response'] ?? null;
        $whatsappLog->save();

        return [
            'status' => $whatsappLog->status === 'sent',
            'message' => $whatsappLog->status === 'sent' ? 'Whatsapp message sent successfully' : 'Whatsapp Message failed to sent',
            'response' => $result,
        ];
    }

    /**
     * Send message directly to WhatsApp API
     */
    public function sendWhatsAppMessage($mobileNo, $message, $type = 'TEXT', $file = '', $templateId = ''): array
    {
        if (config('app.env') !== 'production') {
            $mobileNo = config('services.whatsapp.test_number', '919790124351');
        }

        $payload = [
            'appkey' => config('services.whatsapp.appkey'),
            'authkey' => config('services.whatsapp.authkey'),
            'to' => $mobileNo,
            'message' => $message,
        ];

        $url = config('services.whatsapp.api_url');

        try {
            $response = Http::withHeaders([
                'Accept' => '/',
                'Content-Type' => 'application/json',
                'appkey' => config('services.whatsapp.appkey'),
            ])->withoutVerifying()->post($url, $payload);

            $body = trim($response->body());
            $success = str_contains(strtolower($body), 'true');

            return [
                'status' => $success,
                'response' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp send error: ' . $e->getMessage());

            return [
                'status' => false,
                'response' => $e->getMessage(),
            ];
        }
    }

    /**
     * Replace placeholders like {name}, {month}, etc.
     */
    private function replaceParameters(array $data, string $template): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }
}
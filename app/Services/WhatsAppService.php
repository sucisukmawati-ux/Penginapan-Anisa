<?php

namespace App\Services;

class WhatsAppService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function send(array $payload): array
    {
        $phone = $payload['phone'] ?? null;
        $message = $payload['message'] ?? 'Notifikasi';

        return [
            'recipient' => $phone,
            'message' => $message,
            'status' => 'queued',
            'provider' => 'whatsapp-api',
            'sent_at' => now()->toDateTimeString(),
        ];
    }
}

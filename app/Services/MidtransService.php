<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createTransaction(array $payload): array
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $amount = (int) round((float) ($payload['amount'] ?? 0));

        if ($orderId === '' || $amount < 1) {
            throw new RuntimeException('Order ID dan nominal pembayaran Midtrans harus valid.');
        }

        $request = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
        ];

        foreach (['item_details', 'customer_details', 'callbacks'] as $key) {
            if (isset($payload[$key])) {
                $request[$key] = $payload[$key];
            }
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post($this->snapUrl(), $request)
            ->throw()
            ->json();

        if (! is_array($response) || ! is_string($response['token'] ?? null) || ! is_string($response['redirect_url'] ?? null)) {
            throw new RuntimeException('Respons transaksi Midtrans tidak memiliki token atau URL pembayaran.');
        }

        return [
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => 'pending',
            'redirect_url' => $response['redirect_url'],
            'payment_token' => $response['token'],
            'payment_type' => null,
            'midtrans_response' => $response,
        ];
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    public function verifyNotification(array $notification): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;
        $signature = $notification['signature_key'] ?? null;

        if ($serverKey === '' || ! is_string($orderId) || ! is_string($statusCode)
            || ! is_string($grossAmount) || ! is_string($signature)) {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signature);
    }

    private function snapUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GhtkService
{
    public function quote(array $parameters): array
    {
        $response = $this->client()->get('/services/shipment/fee', $parameters);

        if (! $response->successful() || ! $response->json('success')) {
            throw new RuntimeException($response->json('message') ?: 'Không thể tính phí Giao Hàng Tiết Kiệm.');
        }

        return (array) $response->json('fee');
    }

    private function client(): PendingRequest
    {
        $token = (string) config('services.ghtk.api_token');

        if ($token === '') {
            throw new RuntimeException('GHTK chưa được cấu hình API Token.');
        }

        $headers = ['Token' => $token];
        $clientSource = (string) config('services.ghtk.client_source');
        if ($clientSource !== '') {
            $headers['X-Client-Source'] = $clientSource;
        }

        return Http::baseUrl((string) config('services.ghtk.base_url'))
            ->timeout((int) config('services.ghtk.timeout'))
            ->acceptJson()
            ->withHeaders($headers);
    }
}

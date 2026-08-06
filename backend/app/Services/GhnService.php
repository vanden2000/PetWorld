<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GhnService
{
    public function quote(array $payload): array
    {
        $shopId = (string) config('services.ghn.shop_id');
        if ($shopId === '') {
            throw new RuntimeException('GHN chưa được cấu hình Shop ID.');
        }

        $response = $this->client(['ShopId' => $shopId])
            ->post('/shiip/public-api/v2/shipping-order/fee', $payload);

        if (! $response->successful() || (int) $response->json('code') !== 200) {
            throw new RuntimeException($response->json('message') ?: 'Không thể tính phí Giao Hàng Nhanh.');
        }

        return (array) $response->json('data');
    }

    /** @return array<string, mixed> */
    public function createOrder(array $payload): array
    {
        $shopId = (string) config('services.ghn.shop_id');
        if ($shopId === '') {
            throw new RuntimeException('GHN chưa được cấu hình Shop ID.');
        }

        $response = $this->client(['ShopId' => $shopId])
            ->post('/shiip/public-api/v2/shipping-order/create', $payload);

        if (! $response->successful() || (int) $response->json('code') !== 200) {
            throw new RuntimeException($response->json('message') ?: 'Không thể tạo vận đơn Giao Hàng Nhanh.');
        }

        return (array) $response->json('data');
    }
    /** @return array<int, array<string, mixed>> */
    public function provinces(): array
    {
        return $this->locationRequest('/shiip/public-api/master-data/province');
    }

    /** @return array<int, array<string, mixed>> */
    public function districts(int $provinceId): array
    {
        return $this->locationRequest('/shiip/public-api/master-data/district', [
            'province_id' => $provinceId,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function wards(int $districtId): array
    {
        return $this->locationRequest('/shiip/public-api/master-data/ward', [
            'district_id' => $districtId,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function locationRequest(string $path, array $query = []): array
    {
        $response = $this->locationClient()->get($path, $query);

        if (! $response->successful() || (int) $response->json('code') !== 200) {
            throw new RuntimeException($response->json('message') ?: 'Không thể tải dữ liệu địa chỉ từ GHN.');
        }

        return array_values((array) $response->json('data'));
    }

    private function locationClient(): PendingRequest
    {
        return $this->client();
    }

    private function client(array $headers = []): PendingRequest
    {
        $token = (string) config('services.ghn.api_token');

        if ($token === '') {
            throw new RuntimeException('GHN chưa được cấu hình API Token.');
        }

        return Http::baseUrl((string) config('services.ghn.base_url'))
            ->timeout((int) config('services.ghn.timeout'))
            ->acceptJson()
            ->withHeaders($headers + [
                'Content-Type' => 'application/json',
                'Token' => $token,
            ]);
    }
}

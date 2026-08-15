<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GhnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GhnLocationController extends Controller
{
    public function provinces(GhnService $ghn): JsonResponse
    {
        return response()->json([
            'data' => [
                'provinces' => collect($ghn->provinces())
                    ->map(fn (array $item): array => [
                        'id' => (int) $item['ProvinceID'],
                        'name' => $item['ProvinceName'],
                    ])
                    ->values(),
            ],
        ]);
    }

    public function districts(Request $request, GhnService $ghn): JsonResponse
    {
        $data = $request->validate(['province_id' => ['required', 'integer', 'min:1']]);

        return response()->json([
            'data' => [
                'districts' => collect($ghn->districts($data['province_id']))
                    ->map(fn (array $item): array => [
                        'id' => (int) $item['DistrictID'],
                        'name' => $item['DistrictName'],
                    ])
                    ->values(),
            ],
        ]);
    }

    public function wards(Request $request, GhnService $ghn): JsonResponse
    {
        $data = $request->validate(['district_id' => ['required', 'integer', 'min:1']]);

        return response()->json([
            'data' => [
                'wards' => collect($ghn->wards($data['district_id']))
                    ->map(fn (array $item): array => [
                        'code' => (string) $item['WardCode'],
                        'name' => $item['WardName'],
                    ])
                    ->values(),
            ],
        ]);
    }
}

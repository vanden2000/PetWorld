<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PetSpecies;
use Illuminate\Http\JsonResponse;

class PetSpeciesController extends Controller
{
    public function index(): JsonResponse
    {
        $species = PetSpecies::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'image', 'background_color', 'sort_order', 'show_on_home'])
            ->map(fn(PetSpecies $petSpecies): array => [
                'id' => $petSpecies->id,
                'name' => $petSpecies->name,
                'slug' => $petSpecies->slug,
                'image' => $petSpecies->image,
                'background_color' => $petSpecies->background_color,
                'sort_order' => $petSpecies->sort_order,
                'show_on_home' => $petSpecies->show_on_home,
            ])
            ->all();

        return response()->json(['data' => $species]);
    }
}

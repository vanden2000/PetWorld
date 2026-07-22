<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetSpecies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PetSpeciesController extends Controller
{
    public function index(): View
    {
        $petSpecies = PetSpecies::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.pet-species.index', compact('petSpecies'));
    }

    public function create(): View
    {
        return view('admin.pet-species.create', ['petSpecies' => new PetSpecies()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->ensureHomepageCapacity($data);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('pet-species', 'public');
        }

        PetSpecies::create($data);
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.pet-species')->with('success', 'Đã tạo loài thú cưng.');
    }

    public function edit(PetSpecies $petSpecies): View
    {
        $petSpecies->loadCount('products');

        return view('admin.pet-species.edit', compact('petSpecies'));
    }

    public function update(Request $request, PetSpecies $petSpecies): RedirectResponse
    {
        $data = $this->validatedData($request, $petSpecies);
        $this->ensureHomepageCapacity($data, $petSpecies);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('pet-species', 'public');
        }

        $petSpecies->update($data);
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.pet-species')->with('success', 'Đã cập nhật loài thú cưng.');
    }

    private function validatedData(Request $request, ?PetSpecies $petSpecies = null): array
    {
        $uniqueSlug = 'unique:pet_species,slug' . ($petSpecies ? ',' . $petSpecies->id : '');

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', $uniqueSlug],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'show_on_home' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function ensureHomepageCapacity(array $data, ?PetSpecies $petSpecies = null): void
    {
        if (! $data['show_on_home'] || ! $data['is_active']) {
            return;
        }

        $featuredCount = PetSpecies::query()
            ->where('is_active', true)
            ->where('show_on_home', true)
            ->when($petSpecies, fn ($query) => $query->whereKeyNot($petSpecies->id))
            ->count();

        if ($featuredCount >= 2) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'show_on_home' => 'Trang chủ chỉ hiển thị tối đa 2 loài nổi bật.',
            ]);
        }
    }
}

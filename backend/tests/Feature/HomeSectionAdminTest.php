<?php

namespace Tests\Feature;

use App\Models\HomeSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSectionAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\HomeSectionSeeder::class);
    }

    private function actingAsAdmin()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        return $this->actingAs($admin);
    }

    public function test_admin_can_view_home_sections_management_page(): void
    {
        $response = $this->actingAsAdmin()->get(route('admin.home-sections.index'));

        $response->assertStatus(200);
        $response->assertSee('Quản Lý Hiển Thị Trang Chủ');
        $response->assertSee('hero_slider');
    }

    public function test_admin_can_update_home_sections_configuration(): void
    {
        $sections = HomeSection::all();
        $payload = [];

        foreach ($sections as $index => $section) {
            $payload[$index] = [
                'id' => $section->id,
                'order' => $section->order,
                'custom_title' => $section->key === 'featured_products' ? 'Sản Phẩm Đang Hot' : $section->custom_title,
                'limit' => $section->limit,
            ];
        }

        $response = $this->actingAsAdmin()->put(route('admin.home-sections.update'), [
            'sections' => $payload,
            'active_sections' => [$sections->first()->id],
        ]);

        $response->assertRedirect(route('admin.home-sections.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('home_sections', [
            'key' => 'featured_products',
            'custom_title' => 'Sản Phẩm Đang Hot',
        ]);
    }

    public function test_admin_can_toggle_home_section_status(): void
    {
        $section = HomeSection::first();
        $initialStatus = $section->is_active;

        $response = $this->actingAsAdmin()->patch(route('admin.home-sections.toggle', $section->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('home_sections', [
            'id' => $section->id,
            'is_active' => !$initialStatus,
        ]);
    }
}

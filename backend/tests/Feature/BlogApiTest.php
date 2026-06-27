<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();
    }

    public function test_blog_list_omits_content_and_detail_increments_view_count(): void
    {
        $author = User::create([
            'name' => 'Quản trị viên',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $category = BlogCategory::create([
            'name' => 'Chăm sóc thú cưng',
            'slug' => 'cham-soc-thu-cung',
            'status' => 'active',
        ]);

        $blog = Blog::create([
            'blog_category_id' => $category->id,
            'user_id' => $author->id,
            'title' => 'Chăm sóc chó con',
            'slug' => 'cham-soc-cho-con',
            'description' => 'Mô tả ngắn.',
            'content' => '<article>Nội dung đầy đủ.</article>',
            'image' => 'cham-soc-cho-con.jpg',
            'view_count' => 10,
            'status' => 'active',
        ]);
        Blog::create([
            'blog_category_id' => $category->id,
            'user_id' => $author->id,
            'title' => 'Lịch tiêm phòng',
            'slug' => 'lich-tiem-phong',
            'description' => 'Mô tả liên quan.',
            'content' => '<article>Lịch tiêm phòng.</article>',
            'view_count' => 5,
            'status' => 'active',
        ]);

        $this->getJson('/api/blogs?category=cham-soc-thu-cung&sort=popular')
            ->assertOk()
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.categories.0.blog_count', 2)
            ->assertJsonPath('data.blogs.0.slug', 'cham-soc-cho-con')
            ->assertJsonMissingPath('data.blogs.0.content');

        $this->getJson('/api/blogs/cham-soc-cho-con')
            ->assertOk()
            ->assertJsonPath('data.blog.content', '<article>Nội dung đầy đủ.</article>')
            ->assertJsonPath('data.blog.view_count', 11)
            ->assertJsonPath('data.related_blogs.0.slug', 'lich-tiem-phong')
            ->assertJsonMissingPath('data.related_blogs.0.content');

        $this->assertDatabaseHas('blogs', [
            'id' => $blog->id,
            'view_count' => 11,
        ]);
    }
}

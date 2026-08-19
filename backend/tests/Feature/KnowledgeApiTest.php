<?php

namespace Tests\Feature;

use App\Models\KnowledgeArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();
    }

    public function test_guest_can_list_only_published_knowledge_articles(): void
    {
        KnowledgeArticle::create([
            'title' => 'Hướng dẫn giao hàng',
            'slug' => 'huong-dan-giao-hang',
            'category' => 'shipping',
            'summary' => 'Tóm tắt giao hàng.',
            'content' => '<p>Nội dung giao hàng.</p>',
            'questions' => [],
            'status' => 'published',
            'version' => 1,
            'published_at' => now(),
        ]);
        KnowledgeArticle::create([
            'title' => 'Bản nháp chưa duyệt',
            'slug' => 'ban-nhap-chua-duyet',
            'category' => 'payment',
            'summary' => 'Tóm tắt nháp.',
            'content' => '<p>Nội dung nháp.</p>',
            'questions' => [],
            'status' => 'draft',
            'version' => 1,
            'published_at' => null,
        ]);
        KnowledgeArticle::create([
            'title' => 'Bài đã lưu trữ',
            'slug' => 'bai-da-luu-tru',
            'category' => 'returns',
            'summary' => 'Tóm tắt lưu trữ.',
            'content' => '<p>Nội dung lưu trữ.</p>',
            'questions' => [],
            'status' => 'archived',
            'version' => 1,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/knowledge');

        $response
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.articles.0.slug', 'huong-dan-giao-hang')
            ->assertJsonMissingPath('data.articles.1');
    }

    public function test_guest_can_fetch_published_article_by_slug_with_content(): void
    {
        KnowledgeArticle::create([
            'title' => 'Chính sách bảo mật',
            'slug' => 'chinh-sach-bao-mat',
            'category' => 'privacy',
            'summary' => 'Tóm tắt bảo mật.',
            'content' => '<h2>Thu thập</h2><p>Thông tin cá nhân.</p>',
            'questions' => ['PetWorld thu thập gì?'],
            'status' => 'published',
            'version' => 1,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/knowledge/chinh-sach-bao-mat');

        $response
            ->assertOk()
            ->assertJsonPath('data.article.title', 'Chính sách bảo mật')
            ->assertJsonPath('data.article.content', '<h2>Thu thập</h2><p>Thông tin cá nhân.</p>')
            ->assertJsonPath('data.article.category_label', 'Chính sách bảo mật');
    }

    public function test_draft_article_is_not_visible_by_slug(): void
    {
        KnowledgeArticle::create([
            'title' => 'Bản nháp',
            'slug' => 'ban-nhap',
            'category' => 'terms',
            'summary' => null,
            'content' => '<p>Nháp.</p>',
            'questions' => [],
            'status' => 'draft',
            'version' => 1,
            'published_at' => null,
        ]);

        $this->getJson('/api/knowledge/ban-nhap')->assertNotFound();
    }

    public function test_article_url_follows_category(): void
    {
        KnowledgeArticle::create([
            'title' => 'Điều khoản sử dụng',
            'slug' => 'dieu-khoan-su-dung',
            'category' => 'terms',
            'summary' => 'Tóm tắt.',
            'content' => '<p>Nội dung.</p>',
            'questions' => [],
            'status' => 'published',
            'version' => 1,
            'published_at' => now(),
        ]);
        KnowledgeArticle::create([
            'title' => 'Hướng dẫn voucher',
            'slug' => 'huong-dan-voucher',
            'category' => 'voucher',
            'summary' => 'Tóm tắt.',
            'content' => '<p>Nội dung.</p>',
            'questions' => [],
            'status' => 'published',
            'version' => 1,
            'published_at' => now(),
        ]);

        $list = $this->getJson('/api/knowledge')->json('data.articles');

        $bySlug = collect($list)->keyBy('slug');
        $this->assertSame('/dieu-khoan-su-dung', $bySlug->get('dieu-khoan-su-dung')['url']);
        $this->assertSame('/chinh-sach/huong-dan-voucher', $bySlug->get('huong-dan-voucher')['url']);
    }

    public function test_list_filters_by_category(): void
    {
        foreach (['shipping' => 'slug-ship', 'payment' => 'slug-pay'] as $category => $slug) {
            KnowledgeArticle::create([
                'title' => ucfirst($category),
                'slug' => $slug,
                'category' => $category,
                'summary' => 'Tóm tắt.',
                'content' => '<p>Nội dung.</p>',
                'questions' => [],
                'status' => 'published',
                'version' => 1,
                'published_at' => now(),
            ]);
        }

        $response = $this->getJson('/api/knowledge?category=payment');

        $response
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.articles.0.slug', 'slug-pay');
    }
}
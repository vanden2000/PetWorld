<?php

namespace Tests\Unit;

use App\Support\ProductDescriptionSanitizer;
use PHPUnit\Framework\TestCase;

class ProductDescriptionSanitizerTest extends TestCase
{
    public function test_it_keeps_editor_formatting_and_removes_dangerous_markup(): void
    {
        $html = '<h2 class="ql-align-center" onclick="alert(1)">Tiêu đề</h2>'
            .'<script>alert(1)</script>'
            .'<p><strong>Nội dung</strong> <a href="javascript:alert(1)" target="_blank">xấu</a></p>'
            .'<p><a href="https://petworld.test" target="_blank">an toàn</a></p>';

        $clean = (new ProductDescriptionSanitizer)->sanitize($html);

        $this->assertStringContainsString('<h2 class="ql-align-center">Tiêu đề</h2>', $clean);
        $this->assertStringContainsString('<strong>Nội dung</strong>', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('href="https://petworld.test"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_it_returns_null_for_empty_content(): void
    {
        $this->assertNull((new ProductDescriptionSanitizer)->sanitize('  '));
    }
}

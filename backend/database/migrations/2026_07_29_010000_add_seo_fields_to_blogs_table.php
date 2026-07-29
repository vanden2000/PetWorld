<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('description');
            $table->string('seo_description', 320)->nullable()->after('seo_title');
            $table->string('focus_keyword', 120)->nullable()->after('seo_description');
            $table->string('image_alt')->nullable()->after('image');
            $table->string('canonical_url')->nullable()->after('image_alt');
            $table->boolean('noindex')->default(false)->after('canonical_url');
            $table->timestamp('published_at')->nullable()->after('status');
        });

        // Bài viết đã có sẵn: lấy ngày tạo làm ngày xuất bản để schema Article có datePublished.
        DB::table('blogs')->whereNull('published_at')->update([
            'published_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn([
                'seo_title',
                'seo_description',
                'focus_keyword',
                'image_alt',
                'canonical_url',
                'noindex',
                'published_at',
            ]);
        });
    }
};

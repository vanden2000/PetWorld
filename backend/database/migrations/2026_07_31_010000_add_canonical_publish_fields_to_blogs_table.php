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
            $table->string('canonical_url')->nullable()->after('cover_alt');
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
            $table->dropColumn(['canonical_url', 'noindex', 'published_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        $sourceDirectory = public_path('image/products');
        $targetDirectory = public_path('products');

        if (! File::isDirectory($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true);
        }

        DB::table('images')
            ->where('image_url', 'like', 'products/%')
            ->orderBy('id')
            ->eachById(function (object $image) use ($sourceDirectory, $targetDirectory): void {
                $filename = basename($image->image_url);
                $source = $sourceDirectory.DIRECTORY_SEPARATOR.$filename;
                $target = $targetDirectory.DIRECTORY_SEPARATOR.$filename;

                if (! File::exists($target) && File::exists($source) && ! File::copy($source, $target)) {
                    throw new RuntimeException("Không thể sao chép ảnh sản phẩm: {$filename}");
                }
            });
    }

    public function down(): void
    {
        // Files are retained to avoid breaking images on rollback.
    }
};

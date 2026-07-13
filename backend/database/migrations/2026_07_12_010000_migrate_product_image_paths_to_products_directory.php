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
            ->where('image_url', 'like', 'image/products/%')
            ->orderBy('id')
            ->eachById(function (object $image) use ($sourceDirectory, $targetDirectory): void {
                $filename = basename($image->image_url);
                $source = $sourceDirectory.DIRECTORY_SEPARATOR.$filename;
                $target = $targetDirectory.DIRECTORY_SEPARATOR.$filename;

                if (! File::exists($source) && ! File::exists($target)) {
                    return;
                }

                if (! File::exists($target)) {
                    File::copy($source, $target);
                }

                DB::table('images')
                    ->where('id', $image->id)
                    ->update(['image_url' => 'products/'.$filename]);
            });
    }

    public function down(): void
    {
        // Files are intentionally retained in both folders for a safe rollback.
        DB::table('images')
            ->where('image_url', 'like', 'products/%')
            ->update(['image_url' => DB::raw("CONCAT('image/', image_url)")]);
    }
};

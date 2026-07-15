<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $sourceDirectory = public_path('products');

        if (! File::isDirectory($sourceDirectory)) {
            return;
        }

        foreach (File::files($sourceDirectory) as $file) {
            $destination = 'products/'.$file->getFilename();

            if (! Storage::disk('public')->exists($destination)) {
                Storage::disk('public')->put($destination, File::get($file->getPathname()));
            }
        }
    }

    public function down(): void
    {
        // Product files are retained on the public disk to avoid broken URLs.
    }
};

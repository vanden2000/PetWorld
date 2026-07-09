<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $disk = Storage::disk('public');

        DB::table('categories')
            ->whereNotNull('image')
            ->orderBy('id')
            ->get(['id', 'image'])
            ->each(function (object $category) use ($disk): void {
                $current = ltrim(str_replace('\\', '/', $category->image), '/');
                $filename = basename($current);
                $destination = 'categories/'.$filename;

                // Giữ nguyên mọi file cũ; migration chỉ sao chép sang vị trí chuẩn khi cần.
                if (! $disk->exists($destination)) {
                    foreach ([$current, preg_replace('#^storage/#', '', $current)] as $source) {
                        if ($source && $disk->exists($source)) {
                            $disk->copy($source, $destination);
                            break;
                        }
                    }
                }

                // Một số dữ liệu cũ trỏ trực tiếp vào backend/public/image/categories.
                $legacyPublicFile = public_path('image/categories/'.$filename);
                if (! $disk->exists($destination) && File::isFile($legacyPublicFile)) {
                    $disk->put($destination, File::get($legacyPublicFile));
                }

                // Chỉ đổi database sau khi chắc chắn file đích đã tồn tại.
                if ($disk->exists($destination)) {
                    DB::table('categories')->where('id', $category->id)->update([
                        'image' => $destination,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Không hoàn tác vì định dạng mới vẫn hợp lệ và việc giữ file giúp tránh mất dữ liệu.
    }
};

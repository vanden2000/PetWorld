<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->prefixPaths('banners', 'image', 'image/banners');
        $this->prefixPaths('categories', 'image', 'image');
        $this->prefixPaths('brands', 'image', 'image/brands');
        $this->prefixPaths('blogs', 'image', 'image/blogs');
        $this->prefixPaths('images', 'image_url', 'image/products');
    }

    public function down(): void
    {
        foreach ([
            ['banners', 'image'],
            ['categories', 'image'],
            ['brands', 'image'],
            ['blogs', 'image'],
            ['images', 'image_url'],
        ] as [$table, $column]) {
            DB::table($table)
                ->whereNotNull($column)
                ->orderBy('id')
                ->get(['id', $column])
                ->each(fn (object $row) => DB::table($table)->where('id', $row->id)->update([
                    $column => basename(str_replace('\\', '/', $row->{$column})),
                ]));
        }
    }

    private function prefixPaths(string $table, string $column, string $directory): void
    {
        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->get(['id', $column])
            ->each(function (object $row) use ($table, $column, $directory): void {
                $current = ltrim(str_replace('\\', '/', $row->{$column}), '/');
                $filename = basename($current);

                DB::table($table)->where('id', $row->id)->update([
                    $column => trim($directory, '/').'/'.$filename,
                ]);
            });
    }
};

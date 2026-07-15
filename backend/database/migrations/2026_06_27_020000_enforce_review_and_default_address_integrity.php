<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Chuẩn hóa dữ liệu cũ trước khi thêm constraint để migration không bị gián đoạn.
            DB::table('reviews')->where('rating', '<', 1)->update(['rating' => 1]);
            DB::table('reviews')->where('rating', '>', 5)->update(['rating' => 5]);

            DB::statement(<<<'SQL'
                UPDATE reviews
                INNER JOIN order_items ON reviews.order_item_id = order_items.id
                INNER JOIN orders ON order_items.order_id = orders.id
                SET reviews.status = 'hidden'
                WHERE reviews.user_id <> orders.user_id
                   OR orders.order_status <> 'completed'
            SQL);

            $ratingConstraintExists = DB::table('information_schema.table_constraints')
                ->where('constraint_schema', DB::getDatabaseName())
                ->where('table_name', 'reviews')
                ->where('constraint_name', 'reviews_rating_between_1_and_5')
                ->exists();

            if (! $ratingConstraintExists) {
                DB::statement(
                    'ALTER TABLE reviews '
                    .'ADD CONSTRAINT reviews_rating_between_1_and_5 '
                    .'CHECK (rating BETWEEN 1 AND 5)'
                );
            }
        }

        $duplicateUserIds = DB::table('addresses')
            ->select('user_id')
            ->where('is_default', true)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($duplicateUserIds as $userId) {
            $keepId = DB::table('addresses')
                ->where('user_id', $userId)
                ->where('is_default', true)
                ->max('id');

            DB::table('addresses')
                ->where('user_id', $userId)
                ->where('is_default', true)
                ->where('id', '<>', $keepId)
                ->update(['is_default' => false]);
        }

        if (! Schema::hasColumn('addresses', 'default_user_id')) {
            Schema::table('addresses', function (Blueprint $table): void {
                $table->unsignedBigInteger('default_user_id')->nullable()->after('user_id');
            });

            DB::table('addresses')
                ->where('is_default', true)
                ->update(['default_user_id' => DB::raw('user_id')]);

            Schema::table('addresses', function (Blueprint $table): void {
                $table->unique('default_user_id', 'addresses_one_default_per_user');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('addresses', 'default_user_id')) {
            Schema::table('addresses', function (Blueprint $table): void {
                $table->dropUnique('addresses_one_default_per_user');
                $table->dropColumn('default_user_id');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            $ratingConstraintExists = DB::table('information_schema.table_constraints')
                ->where('constraint_schema', DB::getDatabaseName())
                ->where('table_name', 'reviews')
                ->where('constraint_name', 'reviews_rating_between_1_and_5')
                ->exists();

            if ($ratingConstraintExists) {
                DB::statement('ALTER TABLE reviews DROP CHECK reviews_rating_between_1_and_5');
            }
        }
    }
};

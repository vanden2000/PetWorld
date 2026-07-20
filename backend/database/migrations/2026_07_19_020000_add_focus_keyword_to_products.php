<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('products', fn(Blueprint $t) => $t->string('focus_keyword',120)->nullable()->after('short_description')); } public function down(): void { Schema::table('products', fn(Blueprint $t) => $t->dropColumn('focus_keyword')); } };

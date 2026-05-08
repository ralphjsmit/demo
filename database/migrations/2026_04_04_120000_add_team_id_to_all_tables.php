<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', fn (Blueprint $table) => $table->foreignId('team_id')->nullable()->constrained());
        Schema::table('blog_authors', fn (Blueprint $table) => $table->foreignId('team_id')->nullable()->constrained());
        Schema::table('blog_categories', fn (Blueprint $table) => $table->foreignId('team_id')->nullable()->constrained());
        Schema::table('shop_products', fn (Blueprint $table) => $table->foreignId('team_id')->nullable()->constrained());
        Schema::table('shop_brands', fn (Blueprint $table) => $table->foreignId('team_id')->nullable()->constrained());
        Schema::table('shop_categories', fn (Blueprint $table) => $table->foreignId('team_id')->nullable()->constrained());
        Schema::table('shop_customers', fn (Blueprint $table) => $table->foreignId('team_id')->nullable()->constrained());
        Schema::table('shop_orders', fn (Blueprint $table) => $table->foreignId('team_id')->nullable()->constrained());
    }

    public function down(): void
    {
        Schema::table('blog_posts', fn (Blueprint $table) => $table->dropConstrainedForeignId('team_id'));
        Schema::table('blog_authors', fn (Blueprint $table) => $table->dropConstrainedForeignId('team_id'));
        Schema::table('blog_categories', fn (Blueprint $table) => $table->dropConstrainedForeignId('team_id'));
        Schema::table('shop_products', fn (Blueprint $table) => $table->dropConstrainedForeignId('team_id'));
        Schema::table('shop_brands', fn (Blueprint $table) => $table->dropConstrainedForeignId('team_id'));
        Schema::table('shop_categories', fn (Blueprint $table) => $table->dropConstrainedForeignId('team_id'));
        Schema::table('shop_customers', fn (Blueprint $table) => $table->dropConstrainedForeignId('team_id'));
        Schema::table('shop_orders', fn (Blueprint $table) => $table->dropConstrainedForeignId('team_id'));
    }
};

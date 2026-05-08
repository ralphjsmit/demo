<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('image_id')->nullable()->constrained('filament_media_library');
            $table->json('attachments')->nullable();
        });

        Schema::table('shop_brands', function (Blueprint $table) {
            $table->string('logo')->nullable();
        });

        Schema::create('media_library_item_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_library_item_id')->constrained('filament_media_library');
            $table->foreignId('shop_product_id')->constrained('shop_products');
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_library_item_product');

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_id');
            $table->dropColumn('attachments');
        });

        Schema::table('shop_brands', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};

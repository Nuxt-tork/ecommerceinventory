<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('banner');
            $table->string('cover')->nullable();
            $table->string('invoice')->nullable();
            $table->string('title', 255);
            $table->string('description', 255);
            $table->decimal('price', 10, 2);
            $table->decimal('stock', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->string('color', 7)->nullable();
            $table->date('sell_date')->nullable();
            $table->time('sell_time')->nullable();
            $table->year('sell_year')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->string('use_status')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('product_category_id')->nullable();
            $table->foreign('product_category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('product_images')->onDelete('cascade');
            $table->longText('about')->nullable();
            $table->text('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

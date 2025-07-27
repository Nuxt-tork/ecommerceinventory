<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloths', function (Blueprint $table) {
            $table->id();
            $table->string('banner')->nullable();
            $table->string('thumbnail$');
            $table->string('invoice')->nullable();
            $table->string('title$', 255);
            $table->string('slug', 255);
            $table->string('description', 255)->nullable();
            $table->decimal('stock$', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->string('color$', 7)->nullable();
            $table->date('create_date*$')->nullable();
            $table->time('sell_time')->nullable();
            $table->year('sell_year')->nullable();
            $table->boolean('is_active$')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->string('Gender$#*')->nullable();
            $table->string('status$')->nullable();
            $table->longText('about')->nullable();
            $table->text('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloths');
    }
};

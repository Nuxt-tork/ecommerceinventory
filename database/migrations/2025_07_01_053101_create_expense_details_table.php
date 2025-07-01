<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_details', function (Blueprint $table) {
            $table->id();
            $table->integer('expense_id')->nullable();
            $table->string('model')->nullable();
            $table->integer('model_row_id')->nullable();

            $table->integer('unit_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('total_price')->nullable();
            $table->string('payment_status')->comment('paid/unpaid/partial')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_details');
    }
};

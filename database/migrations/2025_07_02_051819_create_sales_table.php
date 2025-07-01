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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sales_type')->nullable();
            $table->integer('product_id')->nullable();

            $table->integer('unit_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('sell_amount')->comment('unit_price * quantity')->nullable();
            $table->integer('discount')->comment('on sell amount')->nullable();
            $table->integer('sell_amount_after_discount')->comment('unit_price * quantity')->nullable();

            $table->integer('vat')->comment('percent')->nullable();
            $table->integer('vat_amount')->nullable();

            $table->integer('payable')->comment('sell_amount_after_discount + vat_amount')->nullable();
            $table->integer('paid')->nullable();
          
            $table->integer('due_amount')->nullable();
            $table->integer('payment_status')->comment('paid/unpaid/partial')->nullable();

            $table->integer('status')->comment('completed/processing/fail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

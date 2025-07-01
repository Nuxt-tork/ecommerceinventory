<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Product;
use App\Models\SellDue;
use App\Models\ProductStock;
use App\Models\ExpenseDetails;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = new Product();
        $product->title = 'Product 1';
        $product->purchase_price = 100;
        $product->sell_price = 200;
        $product->opening_stock = 50;
        $product->save();

        $stock = new ProductStock();
        $stock->opening_stock   = $product->opening_stock;
        $stock->remaining_stock = $product->opening_stock; // As just initiallized
        $stock->type            = 'add'; // As just initiallized
        $stock->source          = 'product-buy'; // As just initiallized
        $stock->save();

        $expense = new Expense();
        $expense->title = 'Purchased' . $product->title;
        $expense->amount = $product->purchase_price * $product->opening_stock;
        $expense->source = 'product-buy';
        $expense->product_id = $product->id;
        $expense->unit_price = $product->purchase_price;
        $expense->quantity = $product->opening_stock;
        $expense->total_price = $product->purchase_price * $product->opening_stock;
        $expense->payment_status = 'paid';
        $expense->save();

        $sale = new Sale();
        $sale->sales_type = 'product';
        $sale->product_id = $product->id;
        $sale->unit_price = $product->sell_price;
        $sale->quantity  = 10;

        $sale->sell_amount = $sale->unit_price * $sale->quantity;
        $sale->discount = 50;
        $sale->sell_amount_after_discount = $sale->sell_amount - $sale->discount;
        $sale->vat = 5;
        $sale->vat_amount = ($sale->sell_amount_after_discount * $sale->vat) / 100;
        $sale->payable = $sale->sell_amount_after_discount + $sale->vat_amount;
        $sale->paid = 1000;
        $sale->due_amount = $sale->payable - $sale->paid;
        $sale->payment_status = 'partial';
        $sale->status = 'completed';
        $sale->save();

        $due = new SellDue();
        $due->sales_id = $sale->id;
        $due->amount = $sale->due_amount;
        $due->repay_date = '2025-07-17';
        $due->save();

        // Update stock

        $stock = ProductStock::where('product_id', $sale->product_id)->first();

        if (!$stock) {
            // create

            $stock = new ProductStock();
            $stock->product_id = $sale->product_id;
            $stock->opening_stock = $product->opening_stock;
            $stock->remaining_stock = $product->opening_stock;
        }

        $stock->remaining_stock = $stock->remaining_stock - $sale->quantity;
        $stock->save();

        // Income 

        $income = new Income();
        $income->source = 'product-sell';
        $income->sale_id = $sale->id;
        $income->sell_amount = $sale->paid;




        
    }
}

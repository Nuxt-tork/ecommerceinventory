<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseDetails;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
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
        $stock->save();

        $expense = new Expense();
        $expense->title = 'Purchased' . $product->title;
        $expense->amount = $product->purchase_price * $product->opening_stock;
        $expense->save();

        $expense_details = new ExpenseDetails();
        $expense_details->expense_id = $expense->id;
        $expense_details->model = 'Product';
        $expense_details->model_row_id = $product->id;
        $expense_details->unit_price = $product->purchase_price;
        $expense_details->quantity = $product->opening_stock;
        $expense_details->total_price = $product->purchase_price * $product->opening_stock;
        $expense_details->payment_status = 'paid';
        $expense_details->save();
    }
}

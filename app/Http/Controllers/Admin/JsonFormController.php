<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JsonFormController extends Controller
{
    


    public function show()
    {
        $json = json_encode([
            "model_name" => "Product",
            "model_migration_name" => "products",
            "pagination_type" => "backend",
            "generate_admin_interface" => 1,
            "generate_api" => 1,
            "generate_profile" => 0,
            "columns" => [
                "imageType" => ["banner*#", "cover#"],
                "textType" => ["title*#", "description*#"],
                "numberType" => ["price*#", "stock#", "discount#"],
                "colorType" => ["color#"],
                "dateType" => ["sell_date#"],
                "timeType" => ["sell_time#"],
                "yearType" => ["sell_year#"],
                "booleanType" => ["is_active*#", "is_popular#"],
                "selectType" => [
                    [
                        "name" => "use_status",
                        "option_values" => ["new", "used", "refurbished"],
                        "option_labels" => ["New", "Used", "Refurbished"]
                    ],
                    [
                        "name" => "status",
                        "option_values" => ["active", "inactive", "draft"],
                        "option_labels" => ["Active", "Inactive", "Draft"]
                    ]
                ],
                "relationalType" => [
                    [
                        "foreign_key" => "product_category_id",
                        "related_table" => "product_categories",
                        "related_table_id" => "id",
                        "screen_column_of_related_table" => "title"
                    ]
                ],
                "fileType" => ["invoice"],
                "textEditorType" => ["about"],
                "TagType" => ["tags"]
            ],
            "seeder" => [
                [
                    "title" => "Example Product",
                    "description" => "Demo description",
                    "price" => 150,
                    "stock" => 20,
                    "discount" => 10,
                    "color" => "#FF0000",
                    "sell_date" => "2025-07-01",
                    "sell_time" => "10:00:00",
                    "sell_year" => 2025,
                    "is_active" => 1,
                    "is_popular" => 0,
                    "use_status" => "new",
                    "status" => "active",
                    "product_category_id" => 1,
                    "invoice" => "uploads/invoice.pdf",
                    "about" => "<p>About this product...</p>",
                    "tags" => "tag1,tag2"
                ]
            ]
        ], JSON_PRETTY_PRINT);

        return view('admin.crud.json-form', compact('json'));
    }
    public function submit (Request $request) {

    }
}

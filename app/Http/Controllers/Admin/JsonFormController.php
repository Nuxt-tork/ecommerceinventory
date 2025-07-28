<?php

namespace App\Http\Controllers\Admin;

use App\Services\CrudGenerationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JsonFormController extends Controller
{
    /**
     * Display the JSON form with a sample JSON structure.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $json = json_encode([
            "crud_file_name" => "Product-2025-july-24",
            "model_name" => "Product",
            "model_migration_name" => "products",
            "pagination_type" => "backend",
            "generate_admin_interface" => 1,
            "generate_api" => 1,
            "generate_profile" => 0,
            "columns" => [
                "imageType" => ["banner*#$", "cover#"],
                "textType" => ["title*#$", "description*#"],
                "numberType" => ["price*#$", "stock#", "discount#"],
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
                        "type" => "one-to-one",
                        "key" => "belongsTo",
                        "foreign_key" => "product_category_id",
                        "related_model" => "ProductCategory",
                        "related_table" => "product_categories",
                        "related_table_id" => "id",
                        "screen_column_of_related_table" => "title"
                    ],
                    [
                        "type" => "one-to-many",
                        "key" => "hasMany",
                        "foreign_key" => "product_id", // this is in the related table
                        "related_model" => "ProductImage",
                        "related_table" => "product_images",
                        "related_table_id" => "id",
                        "screen_column_of_related_table" => "image_url"
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

    /**
     * Handle the submission of the JSON data and generate the CRUD files.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\CrudGenerationService  $crudGenerationService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, CrudGenerationService $crudGenerationService)
    {
        $request->validate([
            'crud_json' => 'required|json',
        ]);

        $data = json_decode($request->crud_json, true);

        // Set the generation folder path in the service.
        $crudGenerationService->setGenerationFolder($data['crud_file_name'] ?? 'DefaultFile');

        $result = [];

        // Validate that the model name is present.
        $modelName = $data['model_name'] ?? null;
        if (!$modelName) {
            return back()->withErrors('Model name is required.');
        }

        // Generate the model.
        $result['model'] = $crudGenerationService->generateModel($modelName, $data);

        // Validate that the migration name is present.
        $migrationName = $data['model_migration_name'] ?? null;
        if (!$migrationName) {
            return back()->withErrors('Migration name is required.');
        }
        // Generate the migration.
        $result['migration'] = $crudGenerationService->generateMigration($migrationName, $data);

        // Generate the controller.
        $result[$modelName . 'Controller'] = $crudGenerationService->generateController($modelName, $data);

        // Generate the API controller.
        $result[$modelName . 'ApiController'] = $crudGenerationService->generateApiController($modelName, $data);

        // Generate the index view.
        $result[$modelName . 'blade.php'] = $crudGenerationService->generateIndexView($data['columns'], $modelName);

        // Generate the create view.
        $result[$modelName . 'create.blade.php'] = $crudGenerationService->generateCreateView($data['columns'], $modelName);

        // Generate the edit view.
        $result[$modelName . 'edit.blade.php'] = $crudGenerationService->generateEditView($modelName, $data);

        // Process the results and return a response.
        $successMessages = [];
        foreach ($result as $type => $res) {
            if ($res !== true) {
                return back()->withErrors("Error generating {$type}: {$res}");
            }
            $successMessages[] = ucfirst($type) . ' generated successfully';
        }

        return back()->with('success', implode('<br>', $successMessages));
    }

    /**
     * Display the CRUD generator UI.
     *
     * @return \Illuminate\View\View
     */
    public function showCrudGenerator()
    {
        return view('admin.crud.generator');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JsonFormController extends Controller
{
    // Common generation path base folder
    protected $generationBasePath;

    public function __construct()
    {
        // Base folder for all generated files
        $this->generationBasePath = app_path('Generated');

    }


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

    public function submit(Request $request)
    {
        $request->validate([
            'crud_json' => 'required|json',
        ]);

        $data = json_decode($request->crud_json, true);

        // Generate destination generate folder if not 
        $crudFileName = $data['crud_file_name'] ?? 'DefaultFile';
        $generationFolder = $this->generationBasePath . "/{$crudFileName}";
        if (!is_dir($generationFolder)) {
            mkdir($generationFolder, 0755, true);
        }

        // Generate Model
        $modelName = $data['model_name'] ?? null;
        if (!$modelName) {
            return back()->withErrors('Model name is required.');
        }

        $result = $this->generateNewModel($modelName, $generationFolder, $data);

        if ($result !== true) {
            return back()->withErrors($result);
        }

        return back()->with('success', "Model '{$modelName}' created successfully.");
    }


     /**
     * Generate new model in generation folder
     */
    private function generateNewModel(string $modelName, string $generationPath, array $jsonData = [])
    {
        // Validate model name (PascalCase)
        if (!preg_match('/^[A-Z][A-Za-z0-9_]*$/', $modelName)) {
            return "Invalid model name '{$modelName}'. Use PascalCase format.";
        }

        $stubPath = resource_path("blueprints/models/model.stub");

        if (!file_exists($stubPath)) {
            return "Model stub does not exist at '{$stubPath}'.";
        }

        // Load stub and replace placeholder
        $stub = file_get_contents($stubPath);
        $stub = str_replace('{{ClassName}}', $modelName, $stub);

            // Generate relationship methods (if any)
            $relationships = $this->buildRelationships($jsonData['columns']['relationalType'] ?? [], $modelName);
            $stub = str_replace('{{relationships}}', $relationships, $stub);

        // Save the new model file in generation folder
        $modelFilePath = $generationPath . "/{$modelName}.php";

        if (file_exists($modelFilePath)) {
            return "Model file already exists in generation folder.";
        }

        file_put_contents($modelFilePath, $stub);

        return true;
    }

   private function buildRelationships(array $relations, string $modelName): string
    {
        $output = "";

        foreach ($relations as $relation) {
            $relationType     = $relation['key'] ?? null;              // belongsTo, hasMany, etc.
            $foreignKey       = $relation['foreign_key'] ?? null;
            $relatedModel     = $relation['related_model'] ?? null;
            $relatedTable     = $relation['related_table'] ?? null;
            $relatedTableId   = $relation['related_table_id'] ?? 'id';

            if (!$relationType || !$foreignKey || !$relatedModel || !$relatedTable) {
                continue;
            }

            $methodName = \Illuminate\Support\Str::camel($relatedModel);              // e.g. productCategory
            $scopeName  = \Illuminate\Support\Str::studly("with_{$methodName}");      // e.g. WithProductCategory
            $modelTable = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::plural($modelName)); // e.g. products

            // Decide join direction
            if (in_array($relationType, ['hasMany', 'hasOne'])) {
                // Parent → Child
                $left  = "{$modelTable}.id";
                $right = "{$relatedTable}.{$foreignKey}";
            } else {
                // belongsTo or similar (Child → Parent)
                $left  = "{$modelTable}.{$foreignKey}";
                $right = "{$relatedTable}.{$relatedTableId}";
            }

            $output .= <<<EOT

        public function {$methodName}()
        {
            return \$this->{$relationType}(\\App\\Models\\{$relatedModel}::class, '{$foreignKey}', '{$relatedTableId}');
        }

        public function scope{$scopeName}(\$query)
        {
            return \$query->leftJoin('{$relatedTable}', '{$left}', '=', '{$right}');
        }

    EOT;
        }

        return $output;
    }






}

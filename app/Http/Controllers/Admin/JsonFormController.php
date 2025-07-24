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

        // Create generation folder
        $crudFileName = $data['crud_file_name'] ?? 'DefaultFile';
        $generationFolder = $this->generationBasePath . "/{$crudFileName}";
        if (!is_dir($generationFolder)) {
            mkdir($generationFolder, 0755, true);
        }

        $result = [];

        // Generate Model
        $modelName = $data['model_name'] ?? null;
        if (!$modelName) {
            return back()->withErrors('Model name is required.');
        }

        $result['model'] = $this->generateNewModel($modelName, $generationFolder, $data);

        // Generate Migration
        $migrationName = $data['model_migration_name'] ?? null;
        if (!$migrationName) {
            return back()->withErrors('Migration name is required.');
        }

        // Generate Controller

        $result[$modelName . 'Controller'] = $this->generateNewController($modelName, $generationFolder, $data);


        $result['migration'] = $this->generateNewMigration($migrationName, $generationFolder, $data);

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

    private function generateNewMigration(string $migrationName, string $generationPath, array $jsonData)
    {
        $stubPath = resource_path("blueprints/migrations/migration.stub");

        if (!file_exists($stubPath)) {
            return "Migration stub does not exist at '{$stubPath}'.";
        }

        $stub = file_get_contents($stubPath);

        $className = 'Create' . \Illuminate\Support\Str::studly($migrationName) . 'Table';
        $tableName = $migrationName;

        $schema = $this->buildMigrationSchema($jsonData['columns'] ?? []);

        $stub = str_replace(
            ['{{migrationClassName}}', '{{tableName}}', '{{schema}}'],
            [$className, $tableName, $schema],
            $stub
        );

        $filename = now()->format('Y_m_d_His') . "_create_{$tableName}_table.php";
        $filepath = $generationPath . '/' . $filename;

        file_put_contents($filepath, $stub);

        return true;
    }



    private function buildMigrationSchema(array $columns): string
    {
        $lines = [];

        // imageType, fileType → string or nullable string
        foreach (['imageType', 'fileType'] as $type) {
            foreach ($columns[$type] ?? [] as $field) {
                $fieldName = trim(str_replace(['*', '#'], '', $field));
                $isRequired = str_contains($field, '*');
                $lines[] = "\$table->string('{$fieldName}')" . ($isRequired ? '' : '->nullable()') . ';';
            }
        }

        // textType → string
        foreach ($columns['textType'] ?? [] as $field) {
            $fieldName = trim(str_replace(['*', '#'], '', $field));
            $isRequired = str_contains($field, '*');
            $lines[] = "\$table->string('{$fieldName}', 255)" . ($isRequired ? '' : '->nullable()') . ';';
        }

        // numberType → decimal or integer
        foreach ($columns['numberType'] ?? [] as $field) {
            $fieldName = trim(str_replace(['*', '#'], '', $field));
            $isRequired = str_contains($field, '*');
            $lines[] = "\$table->decimal('{$fieldName}', 10, 2)" . ($isRequired ? '' : '->nullable()') . ';';
        }

        // colorType → string (hex)
        foreach ($columns['colorType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->string('{$fieldName}', 7)->nullable();";
        }

        // dateType → date
        foreach ($columns['dateType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->date('{$fieldName}')->nullable();";
        }

        // timeType → time
        foreach ($columns['timeType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->time('{$fieldName}')->nullable();";
        }

        // yearType → year (as integer)
        foreach ($columns['yearType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->year('{$fieldName}')->nullable();";
        }

        // booleanType
        foreach ($columns['booleanType'] ?? [] as $field) {
            $fieldName = trim(str_replace(['*', '#'], '', $field));
            $lines[] = "\$table->boolean('{$fieldName}')->default(false);";
        }

        // selectType → string (or enum if needed)
        foreach ($columns['selectType'] ?? [] as $select) {
            $name = $select['name'];
            $lines[] = "\$table->string('{$name}')->nullable();";
        }

        // relationalType → unsignedBigInteger + foreign key
        foreach ($columns['relationalType'] ?? [] as $relation) {
            $foreignKey = $relation['foreign_key'] ?? null;
            $relatedTable = $relation['related_table'] ?? null;

            if ($foreignKey && $relatedTable) {
                $lines[] = "\$table->unsignedBigInteger('{$foreignKey}')->nullable();";
                $lines[] = "\$table->foreign('{$foreignKey}')->references('id')->on('{$relatedTable}')->onDelete('cascade');";
            }
        }

        // textEditorType → longText
        foreach ($columns['textEditorType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->longText('{$fieldName}')->nullable();";
        }

        // TagType → text
        foreach ($columns['TagType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->text('{$fieldName}')->nullable();";
        }

        return implode("\n            ", $lines); // properly indented
    }

    // Controller methods

    private function generateNewController(string $modelName, string $generationPath, array $jsonData)
    {
        $stubPath = resource_path("blueprints/controllers/controller.stub");

        if (!file_exists($stubPath)) {
            return "Controller stub does not exist at '{$stubPath}'.";
        }

        $stub = file_get_contents($stubPath);

        $routeName = \Illuminate\Support\Str::kebab($modelName);
        $plural = \Illuminate\Support\Str::pluralStudly($modelName);
        $slug = \Illuminate\Support\Str::snake($modelName);

        $columns = $jsonData['columns'] ?? [];

        $stub = str_replace([
            '{{modelName}}',
            '{{plural}}',
            '{{slug}}',
            '{{route}}',
            '{{pagination}}',
            '{{validationRules}}',
            '{{storeAssignments}}',
            '{{fileUploads}}',
            '{{fileUploadsUpdate}}',
        ], [
            $modelName,
            $plural,
            $slug,
            $routeName,
            $jsonData['pagination_type'] === 'backend' ? "\$data = \$data->paginate(\$per_page);" : "\$data = \$data->get();",
            $this->buildValidationRules($columns),
            $this->buildAssignments($columns),
            $this->buildFileUploadCode($columns),
            $this->buildFileUploadCode($columns, true),
        ], $stub);

        $controllerFilename = "{$modelName}Controller.php";
        file_put_contents($generationPath . '/' . $controllerFilename, $stub);

        return true;
    }

    private function buildValidationRules(array $columns): string
    {
        $rules = [];

        foreach ($columns as $type => $fields) {
            if (in_array($type, ['selectType', 'relationalType'])) continue;

            foreach ($fields as $field) {
                $name = str_replace(['*', '#'], '', $field);
                $required = str_contains($field, '*') ? 'required' : 'nullable';

                if ($type === 'imageType' || $type === 'fileType') {
                    $rules[] = "'$name' => '$required|image|mimes:jpeg,png,jpg,gif'";
                } elseif ($type === 'numberType') {
                    $rules[] = "'$name' => '$required|numeric'";
                } elseif ($type === 'booleanType') {
                    $rules[] = "'$name' => '$required|boolean'";
                } elseif ($type === 'dateType') {
                    $rules[] = "'$name' => '$required|date'";
                } else {
                    $rules[] = "'$name' => '$required|string'";
                }
            }
        }

        foreach ($columns['selectType'] ?? [] as $select) {
            $required = str_contains($select['name'], '*') ? 'required' : 'nullable';
            $rules[] = "'{$select['name']}' => '$required'";
        }

        foreach ($columns['relationalType'] ?? [] as $rel) {
            $rules[] = "'{$rel['foreign_key']}' => 'nullable|exists:{$rel['related_table']},id'";
        }

        return implode(",\n            ", $rules);
    }

    private function buildAssignments(array $columns): string
    {
        $assignments = [];

        foreach ($columns as $type => $fields) {
            // Skip file/image types — handled separately
            if (in_array($type, ['imageType', 'fileType'])) continue;

            foreach ($fields as $field) {
                // For types like selectType/relationalType which are arrays
                if (is_array($field) && isset($field['name'])) {
                    $name = $field['name'];
                }
                // For plain string field types like textType, numberType, etc.
                elseif (is_string($field)) {
                    $name = str_replace(['*', '#'], '', $field);
                } else {
                    // Skip invalid/malformed definitions
                    continue;
                }

                $assignments[] = "\$row->{$name} = \$request->{$name};";
            }
        }

        return implode("\n        ", $assignments);
    }


    private function buildFileUploadCode(array $columns, bool $isUpdate = false): string
    {
        $code = '';

        foreach ($columns['imageType'] ?? [] as $field) {
            $name = str_replace(['*', '#'], '', $field);

            $uploadPath = 'storage/' . ($columns['modelName'] ?? 'Uploads');

            $block = <<<EOT

            if (\$request->hasFile('$name')) {
                \$file_response = FileManager::saveFile(
                    \$request->file('$name'),
                    '$uploadPath',
                    ['png', 'jpg', 'jpeg', 'gif']
                );

                if (isset(\$file_response['result']) && !\$file_response['result']) {
                    return back()->with('warning', \$file_response['message']);
                };
    EOT;

            if ($isUpdate) {
                $block .= "\n            FileManager::deleteFile(\$row->{$name});";
            }

            $block .= "\n            \$row->{$name} = \$file_response['filename'];\n        }\n";

            $code .= $block;
        }

        return trim($code);
    }












}

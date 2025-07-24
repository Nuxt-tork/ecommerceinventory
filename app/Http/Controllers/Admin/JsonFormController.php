<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        // Generate model
        $result['model'] = $this->generateNewModel($modelName, $generationFolder, $data);

        // Generate Migration
        $migrationName = $data['model_migration_name'] ?? null;
        if (!$migrationName) {
            return back()->withErrors('Migration name is required.');
        }
        $result['migration'] = $this->generateNewMigration($migrationName, $generationFolder, $data);

        // Generate Controller
        $result[$modelName . 'Controller'] = $this->generateNewController($modelName, $generationFolder, $data);

        // Generate ApiController
        $result[$modelName . 'ApiController'] = $this->generateApiController($modelName, $generationFolder, $data);

        // generate index.blade.php

        $result[$modelName . 'blade.php'] = $this->generateIndexView($data['columns'], $modelName, $generationFolder);

        // generate create.blade.php

        $result[$modelName . 'create.blade.php'] = $this->generateCreateView($data['columns'], $modelName, $generationFolder);


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

    // Generate API controller

    private function generateApiController(string $modelName, string $generationFolder, array $data): bool|string
    {
        if (empty($data['generate_api']) || !$data['generate_api']) {
            return true; // Skip if API generation not requested
        }

        $stubPath = resource_path("blueprints/controllers/apicontroller.stub");

        if (!file_exists($stubPath)) {
            return "API controller stub not found at '{$stubPath}'.";
        }

        $controllerName = "{$modelName}ApiController";
        $controllerPath = $generationFolder . "/{$controllerName}.php";

        if (file_exists($controllerPath)) {
            return "API Controller already exists.";
        }

        $stub = file_get_contents($stubPath);

        // Replace class/model name and resource name
        $stub = str_replace('{{modelName}}', $modelName, $stub);
        $stub = str_replace('{{resource}}', \Illuminate\Support\Str::snake(\Illuminate\Support\Str::plural($modelName)), $stub);

        // Insert validation rules
        $rules = $this->buildValidationRules($data['columns'] ?? []);
        $stub = str_replace('// {{validation_rules}}', $rules, $stub);

        // Insert field assignments
        $assignments = $this->buildAssignments($data['columns'] ?? []);
        $stub = str_replace('// {{assignments}}', $assignments, $stub);

        // Insert file upload handling
        $fileUploads = $this->buildFileUploadCode(array_merge($data['columns'], ['modelName' => $modelName]), false);
        $stub = str_replace('// {{file_uploads}}', $fileUploads, $stub);

        file_put_contents($controllerPath, $stub);

        return true;
    }

    // views

    // private function generateViews(string $modelName, string $route, string $generationPath, array $data): array
    // {
    //     $fields = $this->extractFields($data['columns']);
    //     $visible = $this->extractVisibleFields($data['columns']);

    //     foreach (['index','create','edit','show'] as $view) {
    //         $stub = file_get_contents(resource_path("blueprints/views/{$route}/{$view}.stub"));
    //         $viewHtml = $this->fillTemplate($stub, [
    //             'route' => $route,
    //             'slug' => Str::snake($modelName),
    //             'plural' => Str::pluralStudly($modelName),
    //             'columnsVisible' => $visible,
    //             'fields' => $this->buildFieldInputs($fields),
    //             'action' => $view=='create'?'create':($view=='edit'?'edit':'show'),
    //             'form_action' => $view=='edit'?'update':'store',
    //             'form_id' => $view=='edit'?'$data->id':'',
    //             'form_method' => $view=='edit'?'PUT':'',
    //         ]);
    //         file_put_contents("resources/views/admin/{$route}/{$view}.blade.php", $viewHtml);
    //     }

    //     return ['index','create','edit','show'];
    // }


    // private function extractFields(array $columns): array
    // {
    //     $fields = [];

    //     foreach ($columns as $type => $items) {
    //         // Skip relational types
    //         if ($type === 'relationalType') continue;

    //         foreach ($items as $item) {
    //             if (is_array($item) && isset($item['name'])) {
    //                 $fields[] = $item['name'];
    //             } elseif (is_string($item)) {
    //                 // Remove * and # symbols
    //                 $cleanName = str_replace(['*', '#'], '', $item);
    //                 $fields[] = $cleanName;
    //             }
    //         }
    //     }

    //     return array_unique($fields);
    // }

    private function extractVisibleFields(array $columns): array
    {
        $hiddenFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'password'];

        $visibleFields = [];

        foreach ($columns['fields'] ?? [] as $field => $type) {
            $fieldClean = str_replace(['*', '#', '$'], '', $field);

            // Skip hidden fields
            if (in_array($fieldClean, $hiddenFields)) {
                continue;
            }

            // Optionally only include fields with $ sign (if you want required fields only)
            // if you want all, comment out this block
            // if (!str_contains($field, '$')) {
            //     continue;
            // }

            $visibleFields[$field] = $type;
        }

        return $visibleFields;
    }


    // private function buildFieldInputs(array $columns): string
    // {
    //     $inputs = '';

    //     foreach ($columns as $type => $items) {
    //         foreach ($items as $item) {
    //             $required = '';
    //             $name = '';

    //             if (is_array($item)) {
    //                 $name = str_replace(['*', '#'], '', $item['name']);
    //                 $required = (strpos($item['name'], '*') !== false) ? 'required' : '';
    //             } elseif (is_string($item)) {
    //                 $name = str_replace(['*', '#'], '', $item);
    //                 $required = (strpos($item, '*') !== false) ? 'required' : '';
    //             }

    //             if (!$name) continue;

    //             switch ($type) {
    //                 case 'textType':
    //                 case 'colorType':
    //                 case 'numberType':
    //                 case 'dateType':
    //                 case 'timeType':
    //                 case 'yearType':
    //                     $typeAttr = $type === 'colorType' ? 'color' : ($type === 'numberType' ? 'number' : 'text');
    //                     $inputs .= <<<EOT
    //                 <div class="mb-3">
    //                     <label for="{$name}" class="form-label">{$name}</label>
    //                     <input type="{$typeAttr}" name="{$name}" id="{$name}" class="form-control" {$required}>
    //                 </div>

    //                 EOT;
    //                                     break;

    //                                 case 'booleanType':
    //                                     $inputs .= <<<EOT
    //                 <div class="form-check mb-3">
    //                     <input type="checkbox" name="{$name}" id="{$name}" class="form-check-input" value="1">
    //                     <label for="{$name}" class="form-check-label">{$name}</label>
    //                 </div>

    //                 EOT;
    //                                     break;

    //                                 case 'selectType':
    //                                     $options = '';
    //                                     if (isset($item['option_values']) && isset($item['option_labels'])) {
    //                                         foreach ($item['option_values'] as $index => $value) {
    //                                             $label = $item['option_labels'][$index] ?? $value;
    //                                             $options .= "<option value=\"{$value}\">{$label}</option>";
    //                                         }
    //                                     }

    //                                     $inputs .= <<<EOT
    //                 <div class="mb-3">
    //                     <label for="{$name}" class="form-label">{$name}</label>
    //                     <select name="{$name}" id="{$name}" class="form-select" {$required}>
    //                         {$options}
    //                     </select>
    //                 </div>

    //                 EOT;
    //                                     break;

    //                                 case 'fileType':
    //                                 case 'imageType':
    //                                     $inputs .= <<<EOT
    //                 <div class="mb-3">
    //                     <label for="{$name}" class="form-label">{$name}</label>
    //                     <input type="file" name="{$name}" id="{$name}" class="form-control" {$required}>
    //                 </div>

    //                 EOT;
    //                                     break;

    //                                 case 'textEditorType':
    //                                     $inputs .= <<<EOT
    //                 <div class="mb-3">
    //                     <label for="{$name}" class="form-label">{$name}</label>
    //                     <textarea name="{$name}" id="{$name}" class="form-control" rows="4" {$required}></textarea>
    //                 </div>

    //                 EOT;
    //                                     break;

    //                                 case 'TagType':
    //                                     $inputs .= <<<EOT
    //                 <div class="mb-3">
    //                     <label for="{$name}" class="form-label">{$name}</label>
    //                     <input type="text" name="{$name}" id="{$name}" class="form-control" placeholder="tag1,tag2" {$required}>
    //                 </div>

    //                 EOT;
    //                                     break;
    //                             }
    //                         }
    //                     }

    //     return trim($inputs);
    // }


    // Generate Index View

    private function generateIndexView(array $columns, string $modelName, string $outputPath): bool|string
    {
        $visibleFields = $this->extractVisibleFields($columns);


        $resource = \Illuminate\Support\Str::of($modelName)->plural()->snake();
        $routePrefix = "admin.$resource";

        $thead = "                    <tr>\n                        <th>{{ __('admin.serial') }}</th>\n";
        $tbody = "                        @php \$serial = 1; @endphp\n                        @foreach (\$data as \$row)\n                            <tr>\n                                <td>{{ localize_number(\$serial++) }}</td>\n";

        foreach ($visibleFields as $field) {
            $label = "{{ __('admin.$field') }}";
            $thead .= "                        <th>$label</th>\n";

            $cleanImageFields = array_map(fn($f) => str_replace(['*', '#'], '', $f), $columns['imageType'] ?? []);
            $cleanBooleanFields = array_map(fn($f) => str_replace(['*', '#'], '', $f), $columns['booleanType'] ?? []);

            $isImage = in_array($field, $cleanImageFields);
            $isBoolean = in_array($field, $cleanBooleanFields);

                        if ($isImage) {
                            $tbody .= <<<EOT
                                                <td>
                                                    <div class="my-item d-flex gap-2">
                                                        <div class="my-thumb thumb-md">
                                                            <img src="@if (!empty(\$row->{$field})) {{ asset(\$row->{$field}) }} @else {{ asset(avatarUrl()) }} @endif" alt="image">
                                                        </div>
                                                    </div>
                                                </td>\n
                            EOT;
                        } elseif ($isBoolean) {
                            $tbody .= <<<EOT
                                                <td>
                                                    <div class="form-check form-switch form-switch-md">
                                                        <input type="checkbox" name="{$field}" value="{{ \$row->id }}"
                                                            onclick="toggleSwitchStatus(this, '{$resource}');"
                                                            class="form-check-input" @if (\$row->{$field}) checked @endif>
                                                    </div>
                                                </td>\n
                            EOT;
                        } else {
                            $tbody .= "                                <td>{{ \$row->{$field} }}</td>\n";
                        }
                    }

                    $thead .= "                        <th>{{ __('admin.actions') }}</th>\n                    </tr>";
                    $tbody .= <<<EOT
                                                <td>
                                                    <ul class="my-action__list">
                                                        <li class="my-action">
                                                            <a class="my-action__item my-action__item--success"
                                                                href="{{ route('$routePrefix.show', \$row->id) }}">
                                                                <i class="lni lni-eye"></i>
                                                            </a>
                                                        </li>
                                                        <li class="my-action">
                                                            <a class="my-action__item my-action__item--warning"
                                                                href="{{ route('$routePrefix.edit', \$row->id) }}">
                                                                <i class="lni lni-pencil-alt"></i>
                                                            </a>
                                                        </li>
                                                        <li class="my-action">
                                                            <a onclick="Delete(`{{ route('$routePrefix.destroy', \$row->id) }}`)"
                                                                class="my-action__item my-action__item--danger" href="#">
                                                                <i class="lni lni-trash-can"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        @endforeach
                    EOT;

                    $pagination = <<<EOT

                                        {{ \$data->appends(request()->input())->links() }}
                    EOT;

                    $blade = <<<BLADE
                    @extends('admin.layouts.master')
                    @section('title') {{ \$page_title }} @endsection

                    @section('container')
                    <div class="admin">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="my-table">
                                    <div class="my-table__wrapper">
                                        <div class="my-table__header d-flex justify-content-between">
                                            <div class="my-table__title">
                                                <h5>{{ \$info->title }}</h5>
                                            </div>
                                            <div class="float-right">
                                                @can('$resource-create')
                                                <a href="{{ route(\$info->add_button_route ?? '$routePrefix.create') }}" class="btn btn-primary">
                                                    <i class="flaticon2-add"></i> {{ \$info->add_button_title }}
                                                </a>
                                                @endcan
                                            </div>
                                        </div>

                                        <div class="my-table__body">
                                            <div class="table__top">
                                                @include('admin.common-filters.pagination')
                                                <x-filter.search-bar placeholder="{{ __('admin.search...') }}" />
                                            </div>

                                            @if (\$data->count() > 0)
                                            <table class="table" id="myTable1">
                                                <thead>
                    $thead
                                                </thead>
                                                <tbody>
                    $tbody
                                                </tbody>
                                            </table>
                    $pagination
                                @else
                                <div class="alert alert-custom alert-notice alert-light-success fade show mb-5 text-center" role="alert">
                                    <div class="alert-icon"><i class="flaticon-questions-circular-button"></i></div>
                                    <div class="alert-text text-dark">
                                        {{ __('default.no_data_found') }}
                                        <a href="{{ route(\$info->add_button_route ?? '$routePrefix.create') }}" class="btn btn-success">
                                            <i class="flaticon2-add"></i> {{ __('button.add_now') }}
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.components.modals.delete')
        @endsection
        BLADE;

        // ✅ Ensure path exists
        $viewPath = rtrim($outputPath, '/') . "/{$resource}/index.blade.php";
        if (!is_dir(dirname($viewPath))) {
            mkdir(dirname($viewPath), 0755, true);
        }

        file_put_contents($viewPath, $blade);

        return true;
    }

    // Generate Create.blade.php

    private function generateCreateView(array $columns, string $modelName, string $outputPath): bool|string
    {
        $resource = \Illuminate\Support\Str::of($modelName)->plural()->snake();
        $routePrefix = "admin.$resource";

        $inputs = '';

        // Helper to generate label with required star if needed
        $labelHtml = fn($field, $label, $required = false) => "<label class=\"form-label\" for=\"$field\">{{ __('admin.$label') }}" . ($required ? '<span>&#x002A;</span>' : '') . "</label>";

        foreach ($columns['fields'] ?? [] as $field => $type) {
            // Detect if $ exists in original field key
            $hasDollar = str_contains($field, '$');
            // Clean the field name for id/name/label (remove $, *, #)
            $fieldClean = str_replace(['$', '*', '#'], '', $field);
            // Check if required based on *
            $required = str_contains($field, '*');

            // Wrap inputs in col-md-6 if $ exists, else col-md-12 (adjust as you prefer)
            $colClass = $hasDollar ? 'col-md-6' : 'col-md-12';

            switch (true) {
                case in_array($fieldClean, $columns['imageType'] ?? []):
                    $inputs .= <<<HTML
                                    <div class="$colClass">
                                        <div class="form-group">
                                            {$labelHtml($fieldClean,$fieldClean,$required)}
                                            <div class="admin__thumb-upload">
                                                <div class="admin__thumb-edit">
                                                    <input type="file" class="@error('$fieldClean') is-invalid @enderror" id="$fieldClean" name="$fieldClean" onchange="imagePreview(this,'image_preview_$fieldClean');" accept=".png,.jpg,.jpeg" />
                                                    <label for="$fieldClean"></label>
                                                </div>
                                                <div class="admin__thumb-preview">
                                                    <div id="image_preview_$fieldClean" class="admin__thumb-profilepreview" style="background-image: url({{ asset(avatarUrl()) }});"></div>
                                                </div>
                                                @error('$fieldClean')
                                                    <div class="invalid-feedback">{{ \$message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    HTML;
                                                    break;

                                                case in_array($fieldClean, $columns['selectType'] ?? []):
                                                    $modelClass = $this->inferModelFromField($fieldClean);
                                                    $requiredAttr = $required ? 'required' : '';
                                                    $inputs .= <<<HTML
                                    <div class="$colClass">
                                        <div class="form-group">
                                            {$labelHtml($fieldClean,$fieldClean,$required)}
                                            <select class="form-select search-select @error('$fieldClean') is-invalid @enderror" data-live-search="true" id="$fieldClean" name="$fieldClean" $requiredAttr>
                                                <option value="">{{ __('default.choose') }}</option>
                                                @foreach (activeModelData('$modelClass') as \$row)
                                                    <option value="{{ \$row->id }}" @if(old('$fieldClean') == \$row->id) selected @endif>{{ \$row->title }}</option>
                                                @endforeach
                                            </select>
                                            @error('$fieldClean')
                                                <div class="invalid-feedback">{{ \$message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    HTML;
                                                    break;

                                                case in_array($fieldClean, $columns['booleanType'] ?? []):
                                                    $inputs .= <<<HTML
                                    <div class="$colClass">
                                        <div class="form-group">
                                            {$labelHtml($fieldClean,$fieldClean,$required)}
                                            <div class="form-check form-switch form-switch-md">
                                                <input type="checkbox" class="form-check-input @error('$fieldClean') is-invalid @enderror" id="$fieldClean" name="$fieldClean" value="1" @if(old('$fieldClean')) checked @endif>
                                            </div>
                                            @error('$fieldClean')
                                                <div class="invalid-feedback">{{ \$message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    HTML;
                                                    break;

                                                case $fieldClean === 'body':
                                                    $inputs .= <<<HTML
                                    <div class="$colClass">
                                        <div class="form-group">
                                            {$labelHtml($fieldClean,$fieldClean,$required)}
                                            {!! renderCKEditorHtml('$fieldClean', 0, old('$fieldClean')) !!}
                                            @error('$fieldClean')
                                                <div class="invalid-feedback">{{ \$message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    HTML;
                                                    break;

                                                case in_array($fieldClean, $columns['tagsType'] ?? []):
                                                    $inputs .= <<<HTML
                                    <div class="$colClass">
                                        <div class="form-group">
                                            {$labelHtml($fieldClean,$fieldClean,$required)}
                                            <input type="text" value="{{ old('$fieldClean') }}" class="form-control @error('$fieldClean') is-invalid @enderror" id="$fieldClean" name="$fieldClean" placeholder="{{ __('admin.enter_tags_comma_separated') }}">
                                            @error('$fieldClean')
                                                <div class="invalid-feedback">{{ \$message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    HTML;
                                                    break;

                                                case in_array($type, ['int', 'integer', 'float', 'double', 'number', 'decimal']):
                                                    $requiredAttr = $required ? 'required' : '';
                                                    $inputs .= <<<HTML
                                    <div class="$colClass">
                                        <div class="form-group">
                                            {$labelHtml($fieldClean,$fieldClean,$required)}
                                            <input type="number" value="{{ old('$fieldClean') }}" class="form-control @error('$fieldClean') is-invalid @enderror" id="$fieldClean" name="$fieldClean" placeholder="{{ __('admin.enter') }} {{ __('admin.$fieldClean') }}" $requiredAttr>
                                            @error('$fieldClean')
                                                <div class="invalid-feedback">{{ \$message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    HTML;
                                                    break;

                                                case in_array($type, ['text', 'textarea']):
                                                    $inputs .= <<<HTML
                                    <div class="$colClass">
                                        <div class="form-group">
                                            {$labelHtml($fieldClean,$fieldClean,$required)}
                                            <textarea class="form-control @error('$fieldClean') is-invalid @enderror" id="$fieldClean" name="$fieldClean" rows="4" placeholder="{{ __('admin.enter') }} {{ __('admin.$fieldClean') }}">{{ old('$fieldClean') }}</textarea>
                                            @error('$fieldClean')
                                                <div class="invalid-feedback">{{ \$message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    HTML;
                                                    break;

                                                default:
                                                    $requiredAttr = $required ? 'required' : '';
                                                    $inputs .= <<<HTML
                                    <div class="$colClass">
                                        <div class="form-group">
                                            {$labelHtml($fieldClean,$fieldClean,$required)}
                                            <input type="text" value="{{ old('$fieldClean') }}" class="form-control @error('$fieldClean') is-invalid @enderror" id="$fieldClean" name="$fieldClean" placeholder="{{ __('admin.enter') }} {{ __('admin.$fieldClean') }}" $requiredAttr>
                                            @error('$fieldClean')
                                                <div class="invalid-feedback">{{ \$message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    HTML;
                    break;
            }
        }

        // Use the updated stub path as you requested
        $stubPath = resource_path('blueprints/views/create.stub');
        if (!file_exists($stubPath)) {
            return "Stub file not found: $stubPath";
        }

        $stub = file_get_contents($stubPath);

        // Replace placeholders in stub
        $stub = str_replace('{{inputs}}', $inputs, $stub);
        $stub = str_replace('{{routePrefix}}', $routePrefix, $stub);

        // Write to blade file
        $viewPath = rtrim($outputPath, '/') . "/{$resource}/create.blade.php";
        if (!is_dir(dirname($viewPath))) {
            mkdir(dirname($viewPath), 0755, true);
        }
        file_put_contents($viewPath, $stub);

        return true;
    }

    private function inferModelFromField(string $field): string
    {
        // Remove '_id' suffix
        $base = \Illuminate\Support\Str::of($field)->replace('_id', '');

        // Singularize and convert to StudlyCase
        $modelName = $base->singular()->studly();

        // Return full model namespace (adjust if your models are in a different namespace)
        return "App\\Models\\$modelName";
    }
}

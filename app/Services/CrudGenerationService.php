<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Service class for handling the generation of CRUD files.
 * This class encapsulates the logic for creating models, migrations, controllers, and views
 * based on a JSON definition.
 */
class CrudGenerationService
{
    /**
     * The path to the folder where the generated files will be stored.
     *
     * @var string
     */
    private $generationFolder;

    /**
     * Set the generation folder path and create it if it doesn't exist.
     *
     * @param  string  $crudFileName
     * @return void
     */
    public function setGenerationFolder(string $crudFileName): void
    {
        $this->generationFolder = app_path('Generated') . "/{$crudFileName}";
        if (!is_dir($this->generationFolder)) {
            mkdir($this->generationFolder, 0755, true);
        }
    }

    /**
     * Generate a new model file.
     *
     * @param  string  $modelName
     * @param  array  $jsonData
     * @return bool|string True on success, or an error message string on failure.
     */
    public function generateModel(string $modelName, array $jsonData = [])
    {
        if (!preg_match('/^[A-Z][A-Za-z0-9_]*$/', $modelName)) {
            return "Invalid model name '{$modelName}'. Use PascalCase format.";
        }

        $stubPath = resource_path("blueprints/models/model.stub");

        if (!file_exists($stubPath)) {
            return "Model stub does not exist at '{$stubPath}'.";
        }

        $stub = file_get_contents($stubPath);
        $stub = str_replace('{{ClassName}}', $modelName, $stub);

        $relationships = $this->buildRelationships($jsonData['columns']['relationalType'] ?? [], $modelName);
        $stub = str_replace('{{relationships}}', $relationships, $stub);

        $modelFilePath = $this->generationFolder . "/{$modelName}.php";

        if (file_exists($modelFilePath)) {
            return "Model file already exists in generation folder.";
        }

        file_put_contents($modelFilePath, $stub);

        return true;
    }

    /**
     * Generate a new migration file.
     *
     * @param  string  $migrationName
     * @param  array  $jsonData
     * @return bool|string True on success, or an error message string on failure.
     */
    public function generateMigration(string $migrationName, array $jsonData)
    {
        $stubPath = resource_path("blueprints/migrations/migration.stub");

        if (!file_exists($stubPath)) {
            return "Migration stub does not exist at '{$stubPath}'.";
        }

        $stub = file_get_contents($stubPath);

        $className = 'Create' . Str::studly($migrationName) . 'Table';
        $tableName = $migrationName;

        $schema = $this->buildMigrationSchema($jsonData['columns'] ?? []);

        $stub = str_replace(
            ['{{migrationClassName}}', '{{tableName}}', '{{schema}}'],
            [$className, $tableName, $schema],
            $stub
        );

        $filename = now()->format('Y_m_d_His') . "_create_{$tableName}_table.php";
        $filepath = $this->generationFolder . '/' . $filename;

        file_put_contents($filepath, $stub);

        return true;
    }

    /**
     * Generate a new controller file.
     *
     * @param  string  $modelName
     * @param  array  $jsonData
     * @return bool|string True on success, or an error message string on failure.
     */
    public function generateController(string $modelName, array $jsonData)
    {
        $stubPath = resource_path("blueprints/controllers/controller.stub");

        if (!file_exists($stubPath)) {
            return "Controller stub does not exist at '{$stubPath}'.";
        }

        $stub = file_get_contents($stubPath);

        $routeName = Str::kebab($modelName);
        $plural = Str::pluralStudly($modelName);
        $slug = Str::snake($modelName);

        $columns = $jsonData['columns'] ?? [];

        $stub = str_replace(
            [
                '{{modelName}}',
                '{{plural}}',
                '{{slug}}',
                '{{route}}',
                '{{pagination}}',
                '{{validationRules}}',
                '{{storeAssignments}}',
                '{{fileUploads}}',
                '{{fileUploadsUpdate}}',
            ],
            [
                $modelName,
                $plural,
                $slug,
                $routeName,
                $jsonData['pagination_type'] === 'backend' ? "\$data = \$data->paginate(\$per_page);" : "\$data = \$data->get();",
                $this->buildValidationRules($columns),
                $this->buildAssignments($columns),
                $this->buildFileUploadCode($columns),
                $this->buildFileUploadCode($columns, true),
            ],
            $stub
        );

        $controllerFilename = "{$modelName}Controller.php";
        file_put_contents($this->generationFolder . '/' . $controllerFilename, $stub);

        return true;
    }

    /**
     * Generate a new API controller file.
     *
     * @param  string  $modelName
     * @param  array  $data
     * @return bool|string True on success, or an error message string on failure.
     */
    public function generateApiController(string $modelName, array $data): bool|string
    {
        if (empty($data['generate_api']) || !$data['generate_api']) {
            return true;
        }

        $stubPath = resource_path("blueprints/controllers/apicontroller.stub");

        if (!file_exists($stubPath)) {
            return "API controller stub not found at '{$stubPath}'.";
        }

        $controllerName = "{$modelName}ApiController";
        $controllerPath = $this->generationFolder . "/{$controllerName}.php";

        if (file_exists($controllerPath)) {
            return "API Controller already exists.";
        }

        $stub = file_get_contents($stubPath);

        $stub = str_replace('{{modelName}}', $modelName, $stub);
        $stub = str_replace('{{resource}}', Str::snake(Str::plural($modelName)), $stub);

        $rules = $this->buildValidationRules($data['columns'] ?? []);
        $stub = str_replace('// {{validation_rules}}', $rules, $stub);

        $assignments = $this->buildAssignments($data['columns'] ?? []);
        $stub = str_replace('// {{assignments}}', $assignments, $stub);

        $fileUploads = $this->buildFileUploadCode(array_merge($data['columns'], ['modelName' => $modelName]), false);
        $stub = str_replace('// {{file_uploads}}', $fileUploads, $stub);

        file_put_contents($controllerPath, $stub);

        return true;
    }

    /**
     * Generate the index view file.
     *
     * @param  array  $columns
     * @param  string  $modelName
     * @return bool|string True on success, or an error message string on failure.
     */
    public function generateIndexView(array $columns, string $modelName): bool|string
    {
        $visibleFields = $this->extractVisibleFields($columns);

        $resource = Str::of($modelName)->plural()->snake();
        $routePrefix = "admin.$resource";

        $thead = "                    <tr>\n                        <th>{{ __('admin.serial') }}</th>\n";
        $tbody = "                        @php \$serial = 1; @endphp\n                        @foreach (\$data as \$row)\n                            <tr>\n                                <td>{{ localize_number(\$serial++) }}</td>\n";

        foreach ($visibleFields as $field) {
            $label = "{{ __('admin.$field') }}";
            $thead .= "                        <th>$label</th>\n";

            $cleanImageFields = array_map(fn(\$f) => str_replace(['*', '#'], '', \$f), $columns['imageType'] ?? []);
            $cleanBooleanFields = array_map(fn(\$f) => str_replace(['*', '#'], '', \$f), $columns['booleanType'] ?? []);

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

        $viewPath = rtrim($this->generationFolder, '/') . "/{$resource}/index.blade.php";
        if (!is_dir(dirname($viewPath))) {
            mkdir(dirname($viewPath), 0755, true);
        }

        file_put_contents($viewPath, $blade);

        return true;
    }

    /**
     * Generate the create view file.
     *
     * @param  array  $columns
     * @param  string  $modelName
     * @return bool|string True on success, or an error message string on failure.
     */
    public function generateCreateView(array $columns, string $modelName): bool|string
    {
        $stubPath = resource_path("blueprints/views/create.stub");
        if (!file_exists($stubPath)) {
            return "Stub file not found: {$stubPath}";
        }

        $formInputs = $this->generateFormInputsFromColumns($columns);

        $stub = file_get_contents($stubPath);

        $replaced = str_replace(
            ['{{ modelName }}', '{{ routeName }}', '{{ formInputs }}'],
            [$modelName, Str::kebab($modelName), $formInputs],
            $stub
        );

        $destination = "{$this->generationFolder}/create.blade.php";
        return file_put_contents($destination, $replaced) !== false ?: "Failed to write create view file.";
    }

    /**
     * Generate the edit view file.
     *
     * @param  string  $modelName
     * @param  array  $data
     * @return bool|string True on success, or an error message string on failure.
     */
    public function generateEditView($modelName, $data)
    {
        $columns = $data['columns'] ?? [];

        $stubPath = resource_path("blueprints/views/edit.stub");
        $stub = file_get_contents($stubPath);

        $formFields = $this->generateFormInputsFromColumns($columns, 'edit');

        $replaced = str_replace(
            ['{{ modelName }}', '{{ routeName }}', '{{ formInputs }}'],
            [$modelName, Str::kebab($modelName), $formFields],
            $stub
        );

        $destination = "{$this->generationFolder}/edit.blade.php";

        return file_put_contents($destination, $replaced) !== false
            ? 'Generated successfully'
            : 'Failed to write edit view file.';
    }

    /**
     * Build the relationship methods for the model.
     *
     * @param  array  $relations
     * @param  string  $modelName
     * @return string
     */
    private function buildRelationships(array $relations, string $modelName): string
    {
        $output = "";

        foreach ($relations as $relation) {
            $relationType     = $relation['key'] ?? null;
            $foreignKey       = $relation['foreign_key'] ?? null;
            $relatedModel     = $relation['related_model'] ?? null;
            $relatedTable     = $relation['related_table'] ?? null;
            $relatedTableId   = $relation['related_table_id'] ?? 'id';

            if (!$relationType || !$foreignKey || !$relatedModel || !$relatedTable) {
                continue;
            }

            $methodName = Str::camel($relatedModel);
            $scopeName  = Str::studly("with_{$methodName}");
            $modelTable = Str::snake(Str::plural($modelName));

            if (in_array($relationType, ['hasMany', 'hasOne'])) {
                $left  = "{$modelTable}.id";
                $right = "{$relatedTable}.{$foreignKey}";
            } else {
                $left  = "{$modelTable}.{$foreignKey}";
                $right = "{$relatedTable}.{$relatedTableId}";
            }

            $output .= <<<EOT

        public function {$methodName}()
        {
            return \$this->{$relationType}(\App\Models\{$relatedModel}::class, '{$foreignKey}', '{$relatedTableId}');
        }

        public function scope{$scopeName}(\$query)
        {
            return \$query->leftJoin('{$relatedTable}', '{$left}', '=', '{$right}');
        }

    EOT;
        }

        return $output;
    }

    /**
     * Build the database schema for the migration.
     *
     * @param  array  $columns
     * @return string
     */
    private function buildMigrationSchema(array $columns): string
    {
        $lines = [];

        foreach (['imageType', 'fileType'] as $type) {
            foreach ($columns[$type] ?? [] as $field) {
                $fieldName = trim(str_replace(['*', '#'], '', $field));
                $isRequired = str_contains($field, '*') ? '' : '->nullable()';
                $lines[] = "\$table->string('{$fieldName}')" . $isRequired . ';';
            }
        }

        foreach ($columns['textType'] ?? [] as $field) {
            $fieldName = trim(str_replace(['*', '#'], '', $field));
            $isRequired = str_contains($field, '*') ? '' : '->nullable()';
            $lines[] = "\$table->string('{$fieldName}', 255)" . $isRequired . ';';
        }

        foreach ($columns['numberType'] ?? [] as $field) {
            $fieldName = trim(str_replace(['*', '#'], '', $field));
            $isRequired = str_contains($field, '*') ? '' : '->nullable()';
            $lines[] = "\$table->decimal('{$fieldName}', 10, 2)" . $isRequired . ';';
        }

        foreach ($columns['colorType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->string('{$fieldName}', 7)->nullable();";
        }

        foreach ($columns['dateType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->date('{$fieldName}')->nullable();";
        }

        foreach ($columns['timeType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->time('{$fieldName}')->nullable();";
        }

        foreach ($columns['yearType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->year('{$fieldName}')->nullable();";
        }

        foreach ($columns['booleanType'] ?? [] as $field) {
            $fieldName = trim(str_replace(['*', '#'], '', $field));
            $lines[] = "\$table->boolean('{$fieldName}')->default(false);";
        }

        foreach ($columns['selectType'] ?? [] as $select) {
            $name = $select['name'];
            $lines[] = "\$table->string('{$name}')->nullable();";
        }

        foreach ($columns['relationalType'] ?? [] as $relation) {
            $foreignKey = $relation['foreign_key'] ?? null;
            $relatedTable = $relation['related_table'] ?? null;

            if ($foreignKey && $relatedTable) {
                $lines[] = "\$table->unsignedBigInteger('{$foreignKey}')->nullable();";
                $lines[] = "\$table->foreign('{$foreignKey}')->references('id')->on('{$relatedTable}')->onDelete('cascade');";
            }
        }

        foreach ($columns['textEditorType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->longText('{$fieldName}')->nullable();";
        }

        foreach ($columns['TagType'] ?? [] as $field) {
            $fieldName = trim(str_replace('#', '', $field));
            $lines[] = "\$table->text('{$fieldName}')->nullable();";
        }

        return implode("\n            ", $lines);
    }

    /**
     * Build the validation rules for the controller.
     *
     * @param  array  $columns
     * @return string
     */
    private function buildValidationRules(array $columns): string
    {
        $rules = [];

        foreach ($columns as $type => $fields) {
            if (in_array($type, ['selectType', 'relationalType'])) {
                continue;
            }

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

    /**
     * Build the model property assignments for the controller's store/update methods.
     *
     * @param  array  $columns
     * @return string
     */
    private function buildAssignments(array $columns): string
    {
        $assignments = [];

        foreach ($columns as $type => $fields) {
            if (in_array($type, ['imageType', 'fileType'])) {
                continue;
            }

            foreach ($fields as $field) {
                if (is_array($field) && isset($field['name'])) {
                    $name = $field['name'];
                } elseif (is_string($field)) {
                    $name = str_replace(['*', '#'], '', $field);
                } else {
                    continue;
                }

                $assignments[] = "\$row->{$name} = \$request->{$name};";
            }
        }

        return implode("\n        ", $assignments);
    }

    /**
     * Build the file upload code for the controller.
     *
     * @param  array  $columns
     * @param  bool  $isUpdate
     * @return string
     */
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

    /**
     * Generate the HTML form inputs for the create/edit views.
     *
     * @param  array  $columns
     * @param  string  $mode
     * @return string
     */
    private function generateFormInputsFromColumns(array $columns, string $mode = 'create'): string
    {
        $html = '';

        foreach ($columns as $type => $items) {
            foreach ($items as $key => $item) {
                $fieldName = '';
                $required = false;
                $showInForm = false;

                if (in_array($type, ['selectType', 'relationalType'])) {
                    $rawName = $item['name'] ?? $item['foreign_key'] ?? null;
                    $required = strpos($rawName, '*') !== false;
                    $showInForm = strpos($rawName, '$') !== false;
                    $fieldName = str_replace(['*', '#', '$'], '', $rawName);
                } else {
                    $rawName = $item;
                    $fieldName = str_replace(['*', '#', '$'], '', $item);
                    $required = strpos($rawName, '*') !== false;
                    $showInForm = strpos($rawName, '$') !== false;
                }

                if (!$showInForm || !$fieldName) {
                    continue;
                }

                $label = ucwords(str_replace('_', ' ', $fieldName));
                $requiredStar = $required ? ' <span>*</span>' : '';
                $requiredAttr = $required ? 'required' : '';
                $errorBlade = "@error('{$fieldName}')<div class=\"invalid-feedback\">{{ \$message }}</div>@enderror";

                $value = $mode === 'edit'
                    ? "{{ old('{$fieldName}', \$data->{$fieldName} ?? '') }}"
                    : "{{ old('{$fieldName}') }}";

                switch ($type) {
                    case 'textType':
                    case 'yearType':
                        $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    <input type="text" id="{$fieldName}" name="{$fieldName}" value="{$value}" class="form-control @error('{$fieldName}') is-invalid @enderror" {$requiredAttr}>
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'numberType':
                        $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    <input type="number" id="{$fieldName}" name="{$fieldName}" value="{$value}" class="form-control @error('{$fieldName}') is-invalid @enderror" {$requiredAttr}>
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'booleanType':
                        $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input @error('{$fieldName}') is-invalid @enderror" id="{$fieldName}" name="{$fieldName}" value="1" {{ old('{$fieldName}', \$data->{$fieldName} ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'colorType':
                        $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    <input type="color" class="form-control form-control-color @error('{$fieldName}') is-invalid @enderror" id="{$fieldName}" name="{$fieldName}" value="{$value}">
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'dateType':
                        $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    <input type="date" id="{$fieldName}" name="{$fieldName}" value="{$value}" class="form-control @error('{$fieldName}') is-invalid @enderror" {$requiredAttr}>
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'timeType':
                        $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    <input type="time" id="{$fieldName}" name="{$fieldName}" value="{$value}" class="form-control @error('{$fieldName}') is-invalid @enderror" {$requiredAttr}>
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'imageType':
                    case 'fileType':
                        $accept = $type === 'imageType' ? 'accept="image/*"' : '';
                        $previewUrl = "{{ asset(\$data->{$fieldName} ?? avatarUrl()) }}";

                        $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    <div class="admin__thumb-upload">
                                                        <div class="admin__thumb-edit">
                                                            <input type="file" class="@error('{$fieldName}') is-invalid @enderror" id="{$fieldName}" name="{$fieldName}" {$accept}>
                                                            <label for="{$fieldName}"></label>
                                                        </div>
                                                        <div class="admin__thumb-preview">
                                                            <div id="image_preview_{$fieldName}" class="admin__thumb-profilepreview" style="background-image: url({$previewUrl});"></div>
                                                        </div>
                                                    </div>
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'textEditorType':
                        $html .= <<<HTML
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    {!! renderCKEditorHtml('{$fieldName}', 0, old('{$fieldName}', \$data->{$fieldName} ?? '')) !!}
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'selectType':
                        $optionsHtml = "<option value=\"\">--Choose--</option>\n";
                        foreach ($item['option_values'] as $i => $valueOpt) {
                            $labelOpt = $item['option_labels'][$i];
                            $optionsHtml .= "<option value=\"{$valueOpt}\" {{ old('{$fieldName}', \$data->{$fieldName} ?? '') == '{$valueOpt}' ? 'selected' : '' }}>{$labelOpt}</option>\n";
                        }

                        $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    <select class="form-select @error('{$fieldName}') is-invalid @enderror" id="{$fieldName}" name="{$fieldName}" {$requiredAttr}>
                                                        {$optionsHtml}
                                                    </select>
                                                    {$errorBlade}
                                                </div>
                                            </div>

                        HTML;
                        break;

                    case 'relationalType':
                        if ($item['key'] === 'belongsTo') {
                            $relatedModel = $item['related_model'];
                            $relatedField = $item['screen_column_of_related_table'];

                            $html .= <<<HTML
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="{$fieldName}">{$label}{$requiredStar}</label>
                                                    <select class="form-select search-select @error('{$fieldName}') is-invalid @enderror" id="{$fieldName}" name="{$fieldName}" {$requiredAttr}>
                                                        <option value="">--Choose--</option>
                                                        @foreach (activeModelData(App\\Models\\{$relatedModel}::class) as \$row)
                                                            <option value="{{ \$row->id }}" {{ old('{$fieldName}', \$data->{$fieldName} ?? '') == \$row->id ? 'selected' : '' }}>{{ \$row->{$relatedField} }}</option>
                                                        @endforeach
                                                    </select>
                                                    {$errorBlade}
_                                                </div>
                                            </div>

                            HTML;
                        }
                        break;
                }
            }
        }

        return $html;
    }

    /**
     * Extract the fields that should be visible in the index view table.
     *
     * @param  array  $columns
     * @return array
     */
    private function extractVisibleFields(array $columns): array
    {
        $hiddenFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'password'];

        $visibleFields = [];

        foreach ($columns['fields'] ?? [] as $field => $type) {
            $fieldClean = str_replace(['*', '#', '$'], '', $field);

            if (in_array($fieldClean, $hiddenFields)) {
                continue;
            }

            $visibleFields[$field] = $type;
        }

        return $visibleFields;
    }
}
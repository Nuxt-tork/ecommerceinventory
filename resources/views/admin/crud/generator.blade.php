@extends('admin.layouts.master')
@section('title', 'CRUD Generator')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">CRUD Generator</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('json.form.submit') }}" method="POST" id="crud-generator-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="crud_file_name">CRUD File Name</label>
                                    <input type="text" class="form-control" id="crud_file_name" name="crud_file_name" placeholder="e.g., Product-2025-july-24">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model_name">Model Name</label>
                                    <input type="text" class="form-control" id="model_name" name="model_name" placeholder="e.g., Product">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model_migration_name">Migration Name</label>
                                    <input type="text" class="form-control" id="model_migration_name" name="model_migration_name" placeholder="e.g., products">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pagination_type">Pagination Type</label>
                                    <select class="form-control" id="pagination_type" name="pagination_type">
                                        <option value="backend">Backend</option>
                                        <option value="frontend">Frontend</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="generate_admin_interface" name="generate_admin_interface" value="1" checked>
                                    <label class="form-check-label" for="generate_admin_interface">Generate Admin Interface</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="generate_api" name="generate_api" value="1" checked>
                                    <label class="form-check-label" for="generate_api">Generate API</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="generate_profile" name="generate_profile" value="0">
                                    <label class="form-check-label" for="generate_profile">Generate Profile</label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h4>Columns</h4>
                        <div id="columns-container"></div>
                        <button type="button" class="btn btn-primary" id="add-column">Add Column</button>

                        <hr>

                        <textarea name="crud_json" id="crud_json" class="form-control" rows="20"></textarea>

                        <button type="submit" class="btn btn-success mt-3">Generate CRUD</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const columnsContainer = document.getElementById('columns-container');
        const addColumnButton = document.getElementById('add-column');
        let columnIndex = 0;

        addColumnButton.addEventListener('click', function () {
            const columnHtml = `
                <div class="row column-row" data-index="${columnIndex}">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Column Name</label>
                            <input type="text" class="form-control column-name" placeholder="e.g., title">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Column Type</label>
                            <select class="form-control column-type">
                                <option value="textType">Text</option>
                                <option value="numberType">Number</option>
                                <option value="imageType">Image</option>
                                <option value="fileType">File</option>
                                <option value="colorType">Color</option>
                                <option value="dateType">Date</option>
                                <option value="timeType">Time</option>
                                <option value="yearType">Year</option>
                                <option value="booleanType">Boolean</option>
                                <option value="selectType">Select</option>
                                <option value="relationalType">Relational</option>
                                <option value="textEditorType">Text Editor</option>
                                <option value="TagType">Tag</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input column-required" type="checkbox">
                            <label class="form-check-label">Required</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input column-show-in-form" type="checkbox" checked>
                            <label class="form-check-label">Show in Form</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-column">Remove</button>
                    </div>
                </div>
            `;
            columnsContainer.insertAdjacentHTML('beforeend', columnHtml);
            columnIndex++;
        });

        columnsContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-column')) {
                e.target.closest('.column-row').remove();
            }
        });

        const form = document.getElementById('crud-generator-form');
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const data = {
                crud_file_name: document.getElementById('crud_file_name').value,
                model_name: document.getElementById('model_name').value,
                model_migration_name: document.getElementById('model_migration_name').value,
                pagination_type: document.getElementById('pagination_type').value,
                generate_admin_interface: document.getElementById('generate_admin_interface').checked ? 1 : 0,
                generate_api: document.getElementById('generate_api').checked ? 1 : 0,
                generate_profile: document.getElementById('generate_profile').checked ? 1 : 0,
                columns: {},
                seeder: []
            };

            const columnRows = columnsContainer.querySelectorAll('.column-row');
            columnRows.forEach(row => {
                const name = row.querySelector('.column-name').value;
                const type = row.querySelector('.column-type').value;
                const isRequired = row.querySelector('.column-required').checked;
                const showInForm = row.querySelector('.column-show-in-form').checked;

                if (!data.columns[type]) {
                    data.columns[type] = [];
                }

                let fieldName = name;
                if (isRequired) {
                    fieldName += '*';
                }
                if (showInForm) {
                    fieldName += '$';
                }

                data.columns[type].push(fieldName);
            });

            document.getElementById('crud_json').value = JSON.stringify(data, null, 4);

            form.submit();
        });
    });
</script>
@endpush

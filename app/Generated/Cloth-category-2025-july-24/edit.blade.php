@extends('admin.layouts.master')

@section('title')
    {{ '{{ $info->page_title }}' }}
@endsection

@section('container')
<div class="row">
    <div class="col-lg-12">
        <div class="myy-card">
            <div class="myy-card__wrapper">

                <div class="myy-table__header d-flex justify-content-between">
                    <div class="myy-table__title">
                        <h5>{{ '{{ $info->title }}' }}</h5>
                    </div>
                    <div class="float-right">
                        <a href="{{ '{{ route($info->index_route) }}' }}" class="btn btn-primary">
                            <i class="flaticon2-add"></i>
                            {{ '{{ $info->first_button_title }}' }}
                        </a>
                    </div>
                </div>

                <div class="myy-card__body">
                    <form action="{{ '{{ route($info->form_route, $data->id) }}' }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="thumbnail">Thumbnail <span>*</span></label>
        <div class="admin__thumb-upload">
            <div class="admin__thumb-edit">
                <input type="file" class="@error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/*">
                <label for="thumbnail"></label>
            </div>
            <div class="admin__thumb-preview">
                <div id="image_preview_thumbnail" class="admin__thumb-profilepreview" style="background-image: url({{ asset($data->thumbnail ?? avatarUrl()) }});"></div>
            </div>
        </div>
        @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="title">Title <span>*</span></label>
        <input type="text" id="title" name="title" value="{{ old('title', $data->title ?? '') }}" class="form-control @error('title') is-invalid @enderror" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="stock">Stock <span>*</span></label>
        <input type="number" id="stock" name="stock" value="{{ old('stock', $data->stock ?? '') }}" class="form-control @error('stock') is-invalid @enderror" required>
        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="color">Color</label>
        <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" id="color" name="color" value="{{ old('color', $data->color ?? '') }}">
        @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="create_date">Create Date <span>*</span></label>
        <input type="date" id="create_date" name="create_date" value="{{ old('create_date', $data->create_date ?? '') }}" class="form-control @error('create_date') is-invalid @enderror" required>
        @error('create_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-6">
    <div class="form-group form-check">
        <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" id="is_active" name="is_active" value="1" {{ old('is_active', $data->is_active ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Is Active <span>*</span></label>
        @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="Gender">Gender <span>*</span></label>
        <select class="form-select @error('Gender') is-invalid @enderror" id="Gender" name="Gender" required>
            <option value="">--Choose--</option>
<option value="male" {{ old('Gender', $data->Gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
<option value="female" {{ old('Gender', $data->Gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>

        </select>
        @error('Gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="status">Status</label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" >
            <option value="">--Choose--</option>
<option value="active" {{ old('status', $data->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
<option value="inactive" {{ old('status', $data->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
<option value="draft" {{ old('status', $data->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>

        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

                        </div>

                        <div class="row">
                            <div class="col-lg-10">
                                <button type="submit" class="btn btn-primary mt-4">{{ '{{ __("button.update") }}' }}</button>
                                <button type="reset" class="btn btn-danger mt-4">{{ '{{ __("button.reset") }}' }}</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
    @parent
@endsection

@section('js')
    @parent

@endsection

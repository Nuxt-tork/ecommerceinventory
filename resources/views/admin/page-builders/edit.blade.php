{{-- Extends layout --}}
@extends('admin.layouts.master')

@section('title')
    {{ $info->title }}
@endsection

{{-- Content --}}
@section('container')
    <div class="row">
        <div class="col-lg-12">
            <div class="myy-card">
                <div class="myy-card__wrapper">
                    {{-- Card Header Start --}}
                    <div class=" myy-table__header d-flex justify-content-between">
                        <div class="myy-table__title">
                            <h5>{{ $info->title }}</h5>
                        </div>
                        <div class="float-right">
                            <a href="{{ route($info->first_button_route) }}" class="btn btn-primary">

                                <i class="flaticon2-add"></i>

                                {{ $info->first_button_title }}
                            </a>
                            <a href="{{ route($info->second_button_route) }}" class="btn btn-warning">

                                <i class="flaticon2-add"></i>

                                {{ $info->second_button_title }}
                            </a>
                        </div>
                    </div>
                    {{-- Card Header End --}}

                    {{-- Card Body Start --}}
                    <div class="myy-card__body">
                        <form class="form" action="{{ route($info->form_route, $id) }}" method="post"
                            enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            <div class="row g-4">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="title">Title <span>&#x002A;</span> </label>
                                        <input type="text" value="{{ $data->title }}"
                                            class="form-control @error('title') is-invalid @enderror" id="title"
                                            name="title" placeholder="Enter Title" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="slug">Slug <span>&#x002A;</span> </label>
                                        <input type="text" value="{{ $data->slug }}"
                                            class="form-control @error('slug') is-invalid @enderror" id="slug"
                                            name="slug" placeholder="Enter Slug" required>
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="meta_title">Meta Title </label>
                                        <input type="text" value="{{ $data->meta_title }}"
                                            class="form-control @error('meta_title') is-invalid @enderror" id="meta_title"
                                            name="meta_title" placeholder="Enter Meta Title">
                                        @error('meta_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="meta_tags">Meta Tag </label>
                                        <input type="text" value="{{ $data->meta_tags }}"
                                            class="form-control @error('meta_tags') is-invalid @enderror" id="meta_tags"
                                            name="meta_tags" placeholder="Enter Meta Tag">
                                        @error('meta_tags')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="views">View </label>
                                        <input type="number" value="{{ $data->views }}"
                                            class="form-control @error('views') is-invalid @enderror" id="views"
                                            name="views" placeholder="Enter View">
                                        @error('views')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label class="form-label" for="status">Status <span>&#x002A;</span> </label>
                                        <select class="form-select search-select @error('status') is-invalid @enderror"
                                            data-live-search="true" id="status" name="status" required>
                                            <option value="">--Choose--</option>

                                            <option value="draft" @if ($data->status == 'draft') selected @endif>Draft
                                            </option>

                                            <option value="published" @if ($data->status == 'published') selected @endif>
                                                Published</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input @error('is_edit_disable') is-invalid @enderror"
                                                type="checkbox" name="is_edit_disable" id="is_edit_disable"
                                                @if ($data->is_edit_disable == '1') checked @endif>
                                            <label class="form-check-label" for="is_edit_disable">Is Edit Disable </label>
                                            @error('is_edit_disable')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input @error('is_featured') is-invalid @enderror"
                                                type="checkbox" name="is_featured" id="is_featured"
                                                @if ($data->is_featured == '1') checked @endif>
                                            <label class="form-check-label" for="is_featured">Is Featured </label>
                                            @error('is_featured')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">

                                    <div class="form-group">

                                        <label class="form-label" for="description">Description </label>
                                        {!! renderCKEditorHtml('description', 0, $data->description) !!} @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                                <div class="col-md-12">

                                    <div class="form-group">

                                        <label class="form-label" for="meta_description">Meta Description </label>
                                        {!! renderCKEditorHtml('meta_description', 0, $data->meta_description) !!} @error('meta_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>

                            </div>
                            <div class="row">
                                <div class="col-lg-10">
                                    <button type="submit"
                                        class="btn btn-primary mt-4">{{ __('button.update') }}</button>
                                    <button type="reset" class="btn btn-danger mt-4">{{ __('button.reset') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    {{-- Card Body End --}}
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


    {!! renderCKEditorScript('description') !!}

    {!! renderCKEditorScript('meta_description') !!}

    {{-- SCRIPT --}}'
@endsection

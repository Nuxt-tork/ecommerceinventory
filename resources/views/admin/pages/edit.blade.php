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
        
                                <div class="col-12">
                                <div class="myy-card myy-card--exsm border2 rounded-3">
                                    <div class="myy-card__body">
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
                                        <div class="col-md-12">

                                            <div class="form-group">
        
                                                <label class="form-label" for="content">Content </label>
                                                {!! renderCKEditorHtml('content', 0, $data->content) !!} @error('content')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
        
                                        </div>
                                        <div class="col-md-6">

                                            <div class="form-group">
                                                <label class="form-label" for="author_admin_id">Admin </label>
                                                <select
                                                    class="form-select search-select @error('author_admin_id') is-invalid @enderror"
                                                    data-live-search="true" id="author_admin_id" name="author_admin_id">
                                                    <option value="">--Choose--</option>
                                                    @foreach (activeModelData('App\Models\Admin') as $row)
                                                        <option value="{{ $row->id }}"
                                                            @if ($data->author_admin_id == $row->id) selected @endif>{{ $row->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('author_admin_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
        
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label" for="page_tags">Tag </label>
                                                <input type="text" value="{{ $data->tags }}"
                                                    class="form-control @error('page_tags') is-invalid @enderror"
                                                    name="page_tags" placeholder="Enter Tag">
                                                @error('page_tags')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="upload-file-section-single">
                                            <div class="upload-file">
                                                <label class="upload-file__label">Banner</label>
                                                <div class="upload-file__container">
                                                <div class="upload-file__box dropzone" data-max-size="2MB" data-accepted-files="image/*">
                                                    <input type="file" class="file-input" id="banner" name="banner" accept="image/*" style="display: none;" /> <!-- Remove 'multiple' -->
                                                    <div class="upload-file__icon">
                                                    <span><i class="lni lni-upload"></i></span>
                                                    </div>
                                                    <p class="upload-file__text">
                                                    <span>Click To Upload Image</span> Or Drag And Drop
                                                    </p>
                                                    <small class="upload-file__size" id="banner-size">Max. File size: 2 MB</small>
                                                    @if(isset($data->banner) && $data->banner!='')
                                                        <div class="uploaded-images" style="display: grid;">
                                                            <div class="uploaded-image-preview">
                                                                <img src="{{asset($data->banner)}}" alt="" class="uploaded-file">
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="uploaded-images" style="display: none;"></div>
                                                    @endif
                                                    
                                                </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>


                                    </div>
                                    </div>
                                </div>
                                </div>
        
                                <div class="col-12">
                                <div class="myy-card myy-card--exsm border2 rounded-3">
                                    <div class="myy-card__body">
                                    <div class="row g-4">
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
        
                                        <div class="col-md-12">

                                            <div class="form-group">
        
                                                <label class="form-label" for="meta_description">Meta Description </label>
                                                {!! renderCKEditorHtml('meta_description', 0, $data->meta_description) !!} @error('meta_description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
        
                                        </div>
        
                                        

                                    </div>
                                    </div>
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

  <script src="{{ asset('assets/js/dropzone_initiator.js') }}"></script>

    {!! renderCKEditorScript('content') !!}

    {!! renderCKEditorScript('meta_description') !!}


    {{-- SCRIPT --}}

@endsection

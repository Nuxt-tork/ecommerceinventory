@extends('admin.layouts.master')
@section('title') {{ $page_title }} @endsection

@section('container')
<div class="row">
    <div class="col-lg-12">
        <div class="trk-card">
            <div class="trk-card__wrapper">
                <div class="trk-table__header d-flex justify-content-between">
                    <div class="trk-table__title">
                        <h5>{{ $info->title }}</h5>
                    </div>
                    <div class="float-right">
                        <a href="{{ route($info->first_button_route ?? 'admin.products.index') }}" class="btn btn-primary">
                            <i class="flaticon2-add"></i> {{ $info->first_button_title }}
                        </a>
                    </div>
                </div>

                <div class="trk-card__body">
                    <form class="form" action="{{ route($info->form_route ?? 'admin.products.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        
    </div>
    <div class="row">
        <div class="col-lg-10">
            <button type="submit" class="btn btn-primary mt-4">{{ __('button.create') }}</button>
            <button type="reset" class="btn btn-danger mt-4">{{ __('button.reset') }}</button>
        </div>
    </div>
</form>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('css') @parent @endsection

@section('js') @parent
{!! renderCKEditorScript('body') !!}
<script>
    // Initialize the slug generator for this specific page
    initSlugGenerator('#title', '#slug');
</script>
@endsection

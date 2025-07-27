            @extends('admin.layouts.master')
            @section('title') {{ $page_title }} @endsection

            @section('container')
            <div class="admin">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="my-table">
                            <div class="my-table__wrapper">
                                <div class="my-table__header d-flex justify-content-between">
                                    <div class="my-table__title">
                                        <h5>{{ $info->title }}</h5>
                                    </div>
                                    <div class="float-right">
                                        @can('cloth_categories-create')
                                        <a href="{{ route($info->add_button_route ?? 'admin.cloth_categories.create') }}" class="btn btn-primary">
                                            <i class="flaticon2-add"></i> {{ $info->add_button_title }}
                                        </a>
                                        @endcan
                                    </div>
                                </div>

                                <div class="my-table__body">
                                    <div class="table__top">
                                        @include('admin.common-filters.pagination')
                                        <x-filter.search-bar placeholder="{{ __('admin.search...') }}" />
                                    </div>

                                    @if ($data->count() > 0)
                                    <table class="table" id="myTable1">
                                        <thead>
                                <tr>
                        <th>{{ __('admin.serial') }}</th>
                        <th>{{ __('admin.actions') }}</th>
                    </tr>
                                        </thead>
                                        <tbody>
                                    @php $serial = 1; @endphp
                        @foreach ($data as $row)
                            <tr>
                                <td>{{ localize_number($serial++) }}</td>
                            <td>
                                <ul class="my-action__list">
                                    <li class="my-action">
                                        <a class="my-action__item my-action__item--success"
                                            href="{{ route('admin.cloth_categories.show', $row->id) }}">
                                            <i class="lni lni-eye"></i>
                                        </a>
                                    </li>
                                    <li class="my-action">
                                        <a class="my-action__item my-action__item--warning"
                                            href="{{ route('admin.cloth_categories.edit', $row->id) }}">
                                            <i class="lni lni-pencil-alt"></i>
                                        </a>
                                    </li>
                                    <li class="my-action">
                                        <a onclick="Delete(`{{ route('admin.cloth_categories.destroy', $row->id) }}`)"
                                            class="my-action__item my-action__item--danger" href="#">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                                        </tbody>
                                    </table>
            
                    {{ $data->appends(request()->input())->links() }}
                        @else
                        <div class="alert alert-custom alert-notice alert-light-success fade show mb-5 text-center" role="alert">
                            <div class="alert-icon"><i class="flaticon-questions-circular-button"></i></div>
                            <div class="alert-text text-dark">
                                {{ __('default.no_data_found') }}
                                <a href="{{ route($info->add_button_route ?? 'admin.cloth_categories.create') }}" class="btn btn-success">
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
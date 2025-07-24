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
                                    @can('products-create')
                                    <a href="{{ route($info->first_button_route) }}" class="btn btn-primary">
                                        <i class="flaticon2-add"></i> {{ $info->first_button_title }}
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
                        <th>{{ __('admin.banner') }}</th>
                        <th>{{ __('admin.cover') }}</th>
                        <th>{{ __('admin.title') }}</th>
                        <th>{{ __('admin.description') }}</th>
                        <th>{{ __('admin.price') }}</th>
                        <th>{{ __('admin.stock') }}</th>
                        <th>{{ __('admin.discount') }}</th>
                        <th>{{ __('admin.color') }}</th>
                        <th>{{ __('admin.sell_date') }}</th>
                        <th>{{ __('admin.sell_time') }}</th>
                        <th>{{ __('admin.sell_year') }}</th>
                        <th>{{ __('admin.is_active') }}</th>
                        <th>{{ __('admin.is_popular') }}</th>
                        <th>{{ __('admin.actions') }}</th>
                    </tr>
                                    </thead>
                                    <tbody>
                                @php $serial = 1; @endphp
                        @foreach ($data as $row)
                            <tr>
                                <td>{{ localize_number($serial++) }}</td>
                                <td>{{ $row->banner }}</td>
                                <td>{{ $row->cover }}</td>
                                <td>{{ $row->title }}</td>
                                <td>{{ $row->description }}</td>
                                <td>{{ $row->price }}</td>
                                <td>{{ $row->stock }}</td>
                                <td>{{ $row->discount }}</td>
                                <td>{{ $row->color }}</td>
                                <td>{{ $row->sell_date }}</td>
                                <td>{{ $row->sell_time }}</td>
                                <td>{{ $row->sell_year }}</td>
                                <td>
                                    <div class="form-check form-switch form-switch-md">
                                        <input type="checkbox" name="is_active" value="{{ $row->id }}"
                                            onclick="toggleSwitchStatus(this, 'products');"
                                            class="form-check-input" @if ($row->is_active) checked @endif>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch form-switch-md">
                                        <input type="checkbox" name="is_popular" value="{{ $row->id }}"
                                            onclick="toggleSwitchStatus(this, 'products');"
                                            class="form-check-input" @if ($row->is_popular) checked @endif>
                                    </div>
                                </td>
                                <td>
                                    <ul class="my-action__list">
                                        <li class="my-action">
                                            <a class="my-action__item my-action__item--success"
                                                href="{{ route('admin.products.show', $row->id) }}">
                                                <i class="lni lni-eye"></i>
                                            </a>
                                        </li>
                                        <li class="my-action">
                                            <a class="my-action__item my-action__item--warning"
                                                href="{{ route('admin.products.edit', $row->id) }}">
                                                <i class="lni lni-pencil-alt"></i>
                                            </a>
                                        </li>
                                        <li class="my-action">
                                            <a onclick="Delete(`{{ route('admin.products.destroy', $row->id) }}`)"
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
                                        <a href="{{ route($info->first_button_route) }}" class="btn btn-success">
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
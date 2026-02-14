<script>
    if (!localStorage.getItem('clicked')) {

        localStorage.setItem('url', "{{ route('category.index') }}");

        window.location.href = "{{ route('home') }}";
    }
</script>

@extends('layout.layout')
@section('content')
    @if ($errors->has('name'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('name') }}</div>
    @endif
    @if ($errors->has('image'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('image') }}</div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif

    <section>
        <div class="container-fluid">
            <!-- Trigger the modal with a button -->
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#createModal"><i
                    class="dripicons-plus"></i> {{ trans('file.Add Category') }}</button>&nbsp;
            <button class="btn btn-primary" data-toggle="modal" data-target="#importCategory"><i class="dripicons-copy"></i>
                {{ trans('file.Import Category') }}</button>
        </div>
        <div class="table-responsive">
            <table id="category-table" class="table" style="width: 100%">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Image') }}</th>
                        <th>{{ trans('file.category') }}</th>
                        <th>{{ trans('file.Parent Category') }}</th>
                        <th>{{ trans('file.Number of Product') }}</th>
                        <th>{{ trans('file.Stock Quantity') }}</th>
                        <th>{{ trans('file.Stock Worth (Price/Cost)') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <!-- Create Modal -->
    <div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'category.store', 'method' => 'post', 'files' => true]) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Category') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.name') }} *</label>
                            {{ Form::text('name', null, [
                                'required' => 'required',
                                'class' => 'form-control',
                                'placeholder' => __('file.Type category name'),
                            ]) }}
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Image') }}</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Parent Category') }}</label>
                            <div class="input-group">
                                <input type="text" 
                                       id="parent_category_display_create" 
                                       class="form-control" 
                                       placeholder="{{ __('file.No Parent Category') }}"
                                       readonly
                                       style="background-color: white; cursor: pointer;"
                                       data-toggle="modal" 
                                       data-target="#parentCategorySearchModal">
                                <select name="parent_id" id="parent_id_create" class="d-none">
                                    <option value="">{{ __('file.No Parent Category') }}</option>
                                    @foreach ($lims_category_all as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" 
                                            type="button"
                                            data-toggle="modal" 
                                            data-target="#parentCategorySearchModal"
                                            title="Buscar categoría padre">
                                        <i class="dripicons-search"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" 
                                            type="button"
                                            onclick="$('#parent_id_create').val(''); $('#parent_category_display_create').val('');"
                                            title="Limpiar selección">
                                        <i class="dripicons-cross"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('category.partials-form-actividad')
                    <div class="form-group mt-3">
                        <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    <!-- Edit Modal -->
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {{ Form::open(['route' => ['category.update', 1], 'method' => 'PUT', 'files' => true]) }}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Category') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.name') }} *</label>
                            {{ Form::text('name', null, ['required' => 'required', 'class' => 'form-control']) }}
                        </div>
                        <input type="hidden" name="category_id">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Image') }}</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Parent Category') }}</label>
                            <div class="input-group">
                                <input type="text" 
                                       id="parent_category_display_edit" 
                                       class="form-control" 
                                       placeholder="No {{ trans('file.parent') }}"
                                       readonly
                                       style="background-color: white; cursor: pointer;"
                                       data-toggle="modal" 
                                       data-target="#parentCategorySearchModalEdit">
                                <select name="parent_id" class="d-none" id="parent">
                                    <option value="">No {{ trans('file.parent') }}</option>
                                    @foreach ($lims_category_all as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" 
                                            type="button"
                                            data-toggle="modal" 
                                            data-target="#parentCategorySearchModalEdit"
                                            title="Buscar categoría padre">
                                        <i class="dripicons-search"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" 
                                            type="button"
                                            onclick="$('#parent').val(''); $('#parent_category_display_edit').val('');"
                                            title="Limpiar selección">
                                        <i class="dripicons-cross"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('category.partials-form-edit')

                    <div class="form-group mt-3">
                        <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    <!-- Import Modal -->
    <div id="importCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'category.import', 'method' => 'post', 'files' => true]) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Import Category') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input
                                                                                                                                                                                                fields') }}.</small>
                    </p>
                    <p>{{ trans('file.The correct column order is') }} (name*, parent_category)
                        {{ trans('file.and you must
                                                                                                                                                                                            follow this') }}.
                    </p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('file.Upload CSV File') }} *</label>
                                {{ Form::file('file', ['class' => 'form-control', 'required']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label> {{ trans('file.Sample File') }}</label>
                                <a href="public/sample_file/sample_category.csv" class="btn btn-info btn-block btn-md"><i
                                        class="dripicons-download"></i> {{ trans('file.Download') }}</a>
                            </div>
                        </div>
                    </div>
                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#product").siblings('a').attr('aria-expanded', 'true');
        $("ul#product").addClass("show");
        $("ul#product #category-menu").addClass("active");

        function confirmDelete() {
            if (confirm(
                    "If you delete category all products under this category will also be deleted. Are you sure want to delete?"
                )) {
                return true;
            }
            return false;
        }

        var category_id = [];
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            $('#category-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "category/category-data",
                    dataType: "json",
                    type: "post"
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).attr('data-id', data['id']);
                },
                "columns": [{
                        "data": "key"
                    },
                    {
                        "data": "image"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "parent_id"
                    },
                    {
                        "data": "number_of_product"
                    },
                    {
                        "data": "stock_qty"
                    },
                    {
                        "data": "stock_worth"
                    },
                    {
                        "data": "options"
                    },
                ],
                'language': {
                    'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                    "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                    "search": '{{ trans('file.Search') }}',
                    'paginate': {
                        'previous': '<i class="dripicons-chevron-left"></i>',
                        'next': '<i class="dripicons-chevron-right"></i>'
                    }
                },
                order: [
                    ['2', 'asc']
                ],
                'columnDefs': [{
                        "orderable": false,
                        'targets': [0, 1, 3, 4, 5, 6, 7]
                    },
                    {
                        'render': function(data, type, row, meta) {
                            if (type === 'display') {
                                data =
                                    '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                            }

                            return data;
                        },
                        'checkboxes': {
                            'selectRow': true,
                            'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                        },
                        'targets': [0]
                    }
                ],
                'select': {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                'lengthMenu': [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],

                dom: '<"row"lfB>rtip',
                buttons: [{
                        extend: 'pdf',
                        text: '{{ trans('file.PDF') }}',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                        footer: true
                    },
                    {
                        extend: 'csv',
                        text: '{{ trans('file.CSV') }}',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                        footer: true
                    },
                    {
                        extend: 'print',
                        text: '{{ trans('file.Print') }}',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                        footer: true
                    },
                    {
                        text: '{{ trans('file.delete') }}',
                        className: 'buttons-delete',
                        action: function(e, dt, node, config) {
                            category_id.length = 0;
                            $(':checkbox:checked').each(function(i) {
                                if (i) {
                                    category_id[i - 1] = $(this).closest('tr').data('id');
                                }
                            });
                            if (category_id.length && confirm(
                                    "If you delete category all products under this category will also be deleted. Are you sure want to delete?"
                                )) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'category/deletebyselection',
                                    data: {
                                        categoryIdArray: category_id
                                    },
                                    success: function(data) {
                                        dt.rows({
                                            page: 'current',
                                            selected: true
                                        }).deselect();
                                        dt.rows({
                                            page: 'current',
                                            selected: true
                                        }).remove().draw(false);
                                    }
                                });
                            } else if (!category_id.length)
                                alert('No category is selected!');
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '{{ trans('file.Column visibility') }}',
                        columns: ':gt(0)'
                    },
                ],
            });
        });
    </script>

    {{-- Modal de búsqueda de categoría padre (CREAR) --}}
    @php
        $parentCategoryItems = $lims_category_all->map(function($cat) {
            return [
                'id' => $cat->id,
                'code' => str_pad($cat->id, 4, '0', STR_PAD_LEFT),
                'text' => $cat->name,
                'name' => $cat->name
            ];
        });
    @endphp
    
    @include('partials.search-select-modal', [
        'modalId' => 'parentCategorySearchModal',
        'title' => 'Buscar y Seleccionar Categoría Padre',
        'searchPlaceholder' => 'Buscar por código o nombre...',
        'columns' => ['Código', 'Nombre'],
        'items' => $parentCategoryItems,
        'targetSelectId' => 'parent_id_create',
        'targetInputId' => 'parent_category_display_create'
    ])

    {{-- Modal de búsqueda de categoría padre (EDITAR) --}}
    @include('partials.search-select-modal', [
        'modalId' => 'parentCategorySearchModalEdit',
        'title' => 'Buscar y Seleccionar Categoría Padre',
        'searchPlaceholder' => 'Buscar por código o nombre...',
        'columns' => ['Código', 'Nombre'],
        'items' => $parentCategoryItems,
        'targetSelectId' => 'parent',
        'targetInputId' => 'parent_category_display_edit'
    ])
@endsection

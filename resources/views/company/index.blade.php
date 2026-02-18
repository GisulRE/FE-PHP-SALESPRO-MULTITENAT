@extends('layout.main') @section('content')
@if(session()->has('create_message'))
<div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('create_message') !!}</div>
@endif
@if(session()->has('edit_message'))
<div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('edit_message') }}</div>
@endif
@if(session()->has('delete_message'))
<div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('delete_message') }}</div>
@endif
@if(session()->has('not_permitted'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4>{{ trans('file.Companies') }}</h4>
            </div>
            <div class="card-body">
                @if(in_array('users-add', $all_permission))
                <a href="{{ route('companies.create') }}" class="btn btn-info mb-3"><i class="dripicons-plus"></i> Agregar Empresa</a>
                @endif
                <div class="table-responsive">
                    <table id="company-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ trans('file.name') }}</th>
                                <th>Fecha de Creación</th>
                                <th class="not-exported">{{ trans('file.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $company)
                            <tr>
                                <td>{{ $company->id }}</td>
                                <td>{{ $company->name }}</td>
                                <td>{{ $company->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        @if(in_array('users-edit', $all_permission))
                                        <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-sm btn-primary" title="Editar"><i class="dripicons-document-edit"></i></a>
                                        @endif
                                        @if(in_array('users-delete', $all_permission))
                                        <form action="{{ route('companies.destroy', $company->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar esta empresa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="dripicons-trash"></i></button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function() {
        $('#company-table').DataTable({
            "order": [[0, "desc"]],
            'language': {
                'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
                "info": '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{trans("file.Search")}}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'columnDefs': [{
                "orderable": false,
                'targets': [3]
            }],
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
                    text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    }
                },
                {
                    extend: 'csv',
                    text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: '<i title="print" class="fa fa-print"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i title="column visibility" class="fa fa-eye"></i>',
                    columns: ':gt(0)'
                },
            ],
        });
    });
</script>
@endsection

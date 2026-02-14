@extends('layout.main')
@section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section>
        <div class="container-fluid">
        </div>
        <div class="table-responsive">
            <table id="cashier-table" class="table purchase-list">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Account') }}</th>
                        <th>Estado</th>
                        <th>Monto Apertura</th>
                        <th>Monto Cierre</th>
                        <th>Total Ingreso</th>
                        <th>{{ trans('file.Date') }} Apertura</th>
                        <th>{{ trans('file.Date') }} Cierre</th>
                        <th>{{ trans('file.Note') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_cashier_all as $key => $cashier)
                        <tr data-id="{{ $cashier->id }}">
                            <td>{{ $key }}</td>
                            <?php $account = DB::table('accounts')->find($cashier->account_id); ?>
                            <td>{{ $account->name }}</td>
                            @if ($cashier->is_active)
                                <td>
                                    <div class="badge badge-success">Abierto</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-danger">Cerrado</div>
                                </td>
                            @endif
                            <td>{{ number_format($cashier->amount_start, 2) }}</td>
                            <td>{{ number_format($cashier->amount_end, 2) }}</td>
                            @if ($cashier->amount_end == null)
                                <td>{{ number_format(0, 2) }}</td>
                            @else
                                <td>{{ number_format($cashier->amount_end - $cashier->amount_start, 2) }}</td>
                            @endif
                            <td>{{ date('d-m-Y H:i:s', strtotime($cashier->start_date)) }}</td>
                            @if ($cashier->end_date != null)
                                <td>{{ date('d-m-Y H:i:s', strtotime($cashier->end_date)) }}</td>
                            @else
                                <td> Aún no Definido</td>
                            @endif
                            <td>{{ $cashier->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    <script type="text/javascript">
        $("ul#account").siblings('a').attr('aria-expanded', 'true');
        $("ul#account").addClass("show");
        $("ul#account #cashier_account-list-menu").addClass("active");

        function confirmDelete() {
            if (confirm("¿Está seguro de eliminar?")) {
                return true;
            }
            return false;
        }

        var adjustment_id = [];
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table = $('#cashier-table').DataTable({
            "order": [],
            'language': {
                'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ trans('file.Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'columnDefs': [{
                    "orderable": false,
                    'targets': [0, 6]
                },
                {
                    'checkboxes': {
                        'selectRow': true
                    },
                    'targets': 0
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
                /*{
                    text: '{{ trans('file.delete') }}',
                    className: 'buttons-delete',
                    action: function ( e, dt, node, config ) {
                            cashier_id.length = 0;
                            $(':checkbox:checked').each(function(i){
                                if(i){
                                    cashier_id[i-1] = $(this).closest('tr').data('id');
                                }
                            });
                            if(cashier_id.length && confirm("¿Está Seguro de Eliminar Elementos Seleccionados?")) {
                                $.ajax({
                                    type:'POST',
                                    url:'cashier/deletebyselection',
                                    data:{
                                        cashierIdArray: cashier_id
                                    },
                                    success:function(data){
                                        alert(data);
                                    }
                                });
                                dt.rows({ page: 'current', selected: true }).remove().draw(false);
                            }
                            else if(!cashier_id.length)
                                alert('Ningun Regitro Seleccionado!');
                        }
                    }
                },*/
                {
                    extend: 'colvis',
                    text: '{{ trans('file.Column visibility') }}',
                    columns: ':gt(0)'
                },
            ],
        });
    </script>
@endsection

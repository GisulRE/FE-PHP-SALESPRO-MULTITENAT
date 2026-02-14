@extends('layout.main') @section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">{{ trans('file.Payment Report') }}</h3>
                </div>
                {!! Form::open(['route' => 'report.paymentByDate', 'method' => 'post']) !!}
                <div class="col-md-8 offset-md-2 mt-3 mb-3">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label>Desde</label>
                            <input name="start_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                value="{{ $start_date }}" required>
                        </div>
                        <div class="col-md-3">
                            <label>Hasta</label>
                            <input name="end_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                value="{{ $end_date }}" required>
                        </div>
                        <div class="col-md-3">
                            <label>Filtro</label>
                            <select id="kind_payment" name="kind_payment" class="form-control" value="{{ $kind_payment }}">
                                <option value="1">{{ trans('file.Sale') }}</option>
                                <option value="2">{{ trans('file.Purchase') }}</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label> &nbsp;</label>
                            <button class="btn btn-primary fa fa-search" type="submit" style="margin-left: 5px">
                                {{ trans('file.Search') }}</button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        <div class="table-responsive mb-4">
            <table id="report-table" class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ trans('file.Date') }}</th>
                        <th>{{ trans('file.Payment Reference') }} </th>
                        @if ($kind_payment == 1)
                            <th>{{ trans('file.Sale Reference') }}</th>
                        @elseif ($kind_payment == 2)
                            <th>{{ trans('file.Purchase Reference') }}</th>
                        @else
                            <th>{{ trans('file.Sale Reference') }}</th>
                            <th>{{ trans('file.Purchase Reference') }}</th>
                        @endif
                        <th>{{ trans('file.Due') }}</th>
                        <th>{{ trans('file.Amount') }}</th>
                        <th>{{ trans('file.Paid By') }}</th>
                        <th>{{ trans('file.Created By') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report_data_list as $key => $data)
                        <?php
                        $key++;
                        $user = DB::table('users')->find($data->user_id);
                        ?>
                        <tr>
                            <td>{{ $key++ }}</td>
                            <td>{{ $data->date }}
                            </td>
                            <td>{{ $data->reference_payment }}</td>
                            @if ($kind_payment == 1)
                                <td>
                                    {{ $data->reference_sale }}
                                </td>
                                <td>{{ $data->due }}</td>
                            @elseif ($kind_payment == 2)
                                <td>
                                    {{ $data->reference_purchase }}
                                </td>
                                <td>{{ $data->due }}</td>
                            @endif
                            <td>{{ $data->amount }}</td>
                            <td>{{ $data->method }}</td>
                            @if ($user)
                                <td>{{ $user->name }}<br>{{ $user->email }}</td>
                            @else
                                <td>Usuario No Disponible</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <script type="text/javascript">
        $("ul#report").siblings('a').attr('aria-expanded', 'true');
        $("ul#report").addClass("show");
        $("ul#report li#payment-report-menu").addClass("active");
        $("#kind_payment").val({{ $kind_payment }});

        $('#report-table').DataTable({
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
                'targets': 0
            }, ],
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
                    },
                },
                {
                    extend: 'excel',
                    text: '<span class="fa fa-file-excel-o"> Excel</span>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                    },
                },
                {
                    extend: 'csv',
                    text: '{{ trans('file.CSV') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                    },
                },
                {
                    extend: 'print',
                    text: '{{ trans('file.Print') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                    },
                },
                {
                    extend: 'colvis',
                    text: '{{ trans('file.Column visibility') }}',
                    columns: ':gt(0)'
                }
            ],
        });
    </script>
@endsection

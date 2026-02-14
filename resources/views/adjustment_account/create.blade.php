@extends('layout.main')
@section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{trans('file.Add Adjustment')}}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{trans('file.The field labels marked with * are required input fields')}}.</small>
                            </p>
                            @if(request()->has('from_report') && request()->get('from_report') == 'service_commission_qr')
                                @php
                                    $startDate = request()->get('start_date', '');
                                    $endDate = request()->get('end_date', '');
                                    $startDateFormatted = $startDate ? date('d/m/Y', strtotime($startDate)) : 'N/A';
                                    $endDateFormatted = $endDate ? date('d/m/Y', strtotime($endDate)) : 'N/A';
                                @endphp
                                <div class="alert alert-info">
                                    <strong>Datos del Reporte de Comisiones QR:</strong><br>
                                    Total Comisión por QR: <strong>{{ request()->get('amount', '0.00') }} Bs.</strong><br>
                                    Período: del <strong>{{ $startDateFormatted }}</strong> al
                                    <strong>{{ $endDateFormatted }}</strong>
                                </div>
                            @endif
                            {!! Form::open(['route' => 'adjustment_account.store', 'method' => 'post', 'id' => 'adjustment-form']) !!}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Account')}} *</label>
                                                <input type="hidden" name="ajax" value="false">
                                                <select required id="account_id" name="account_id"
                                                    class="selectpicker form-control" data-live-search="true"
                                                    data-live-search-style="begins" title="Seleccione Cuenta...">
                                                    @foreach($lims_accounts_list as $account)
                                                        <option value="{{$account->id}}">{{$account->name}}
                                                            [{{$account->account_no}}]</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Type Adjustment')}} *</label>
                                                <select required id="type_adjustment_id" name="type_adjustment"
                                                    class="selectpicker form-control" title="Seleccione un Ajuste...">
                                                    <option value="ING">Ingreso</option>
                                                    <option value="EGR">Egreso</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Amount')}} *</label>
                                                <input required type="number" name="amount" step="any" class="form-control"
                                                    value="{{ request()->get('amount', '') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>{{trans('file.Note')}}</label>
                                                @php
                                                    $noteText = '';
                                                    if (request()->has('from_report') && request()->get('from_report') == 'service_commission_qr') {
                                                        $startDate = request()->get('start_date', '');
                                                        $endDate = request()->get('end_date', '');
                                                        $startDateFormatted = $startDate ? date('d/m/Y', strtotime($startDate)) : 'N/A';
                                                        $endDateFormatted = $endDate ? date('d/m/Y', strtotime($endDate)) : 'N/A';
                                                        $amount = request()->get('amount', '0.00');
                                                        $noteText = "Ajuste generado desde Reporte de Comisiones de Servicios.\nPeríodo: del {$startDateFormatted} al {$endDateFormatted}\nTotal Comisión por QR: {$amount} Bs.";
                                                    }
                                                @endphp
                                                <textarea required rows="5" class="form-control"
                                                    name="note">{{ $noteText }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary"
                                            id="submit-button">
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript">
        $("ul#product").siblings('a').attr('aria-expanded', 'true');
        $("ul#product").addClass("show");
        $("ul#product #adjustment_account-create-menu").addClass("active");
        // array data depend on warehouse

        $('.selectpicker').selectpicker({
            style: 'btn-link',
        });
        $(window).keydown(function (e) {
            if (e.which == 13) {
                var $targ = $(e.target);
                if (!$targ.is("textarea") && !$targ.is(":button,:submit")) {
                    var focusNext = false;
                    $(this).find(":input:visible:not([disabled],[readonly]), a").each(function () {
                        if (this === e.target) {
                            focusNext = true;
                        }
                        else if (focusNext) {
                            $(this).focus();
                            return false;
                        }
                    });
                    return false;
                }
            }
        });
    </script>
@endsection
<h1>{{ trans('file.Payment Details') }}</h1>
<p><strong>{{ trans('file.Sale Reference') }}: </strong>{{$sale_reference}}</p>
<p><strong>{{ trans('file.Payment Reference') }}: </strong>{{$payment_reference}}</p>
<p><strong>{{ trans('file.Payment Method') }}: </strong>{{$payment_method}}</p>
<p><strong>{{ trans('file.grand total') }}: </strong>{{$grand_total}} {{$general_setting->currency}}</p>
<p><strong>{{ trans('file.Paid Amount') }}: </strong>{{$paid_amount}} {{$general_setting->currency}}</p>
<p><strong>{{ trans('file.Due') }}: </strong>{{number_format((float)($grand_total - $paid_amount), 2, '.', '')}} {{$general_setting->currency}}</p>
<p>{{ trans('file.Thank You') }}</p>

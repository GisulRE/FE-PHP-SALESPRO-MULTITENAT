<h1>Hey {{$name}}!</h1>
<p>{{ trans('file.Reference No') }}: {{$reference_no}}</p>
<p>{{ trans('file.Your payroll is') }}: {{$amount}} {{$general_setting->currency}}</p>
<p>{{ trans('file.Thank You') }}</p>
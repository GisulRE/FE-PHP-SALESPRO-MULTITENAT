<h1>{{ trans('file.Delivery Details') }}</h1>
<h3>{{ trans('file.Dear') }} {{$customer}},</h3>
@if($status == 2)
	<p>{{ trans('file.Your Product is Delivering') }}.</p>
@else
	<p>{{ trans('file.Your Product is Delivered') }}.</p>
@endif
<p><strong>{{ trans('file.Sale Reference') }}: </strong>{{$sale_reference}}</p>
<p><strong>{{ trans('file.Delivery Reference') }}: </strong>{{$delivery_reference}}</p>
<p><strong>{{ trans('file.Destination') }}: </strong>{{$address}}</p>
@if($delivered_by)
<p><strong>{{ trans('file.Delivered By') }}: </strong>{{$delivered_by}}</p>
@endif
<p>{{ trans('file.Thank You') }}</p>
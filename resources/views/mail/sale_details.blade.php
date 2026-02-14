<h1>{{ trans('file.Sale Details') }}</h1>
<p><strong>{{ trans('file.Sale Reference') }}: </strong>{{$reference_no}}</p>
<p>
	<strong>{{ trans('file.Sale Status') }}: </strong>
	@if($sale_status==1){{trans('file.Completed')}}
	@elseif($sale_status==2){{trans('file.Pending')}}
	@endif
</p>
<p>
	<strong>{{ trans('file.Payment Status') }}: </strong>
	@if($payment_status==1){{trans('file.Pending')}}
	@elseif($payment_status==2){{trans('file.Due')}}
	@elseif($payment_status==3){{trans('file.Partial')}}
	@else{{trans('file.Paid')}}@endif
</p>
<h3>{{ trans('file.Order Table') }}</h3>
<table style="border-collapse: collapse; width: 100%;">
	<thead>
		<th style="border: 1px solid #000; padding: 5px">#</th>
		<th style="border: 1px solid #000; padding: 5px">{{ trans('file.product') }}</th>
		<th style="border: 1px solid #000; padding: 5px">Link {{ trans('file.Download') }}</th>
		<th style="border: 1px solid #000; padding: 5px">{{ trans('file.qty') }}</th>
		<th style="border: 1px solid #000; padding: 5px">{{ trans('file.Unit Price') }}</th>
		<th style="border: 1px solid #000; padding: 5px">SubTotal</th>
	</thead>
	<tbody>
		@foreach($products as $key=>$product)
		<tr>
			<td style="border: 1px solid #000; padding: 5px">{{$key+1}}</td>
			<td style="border: 1px solid #000; padding: 5px">{{$product}}</td>
			@if($file[$key])
				<td style="border: 1px solid #000; padding: 5px"><a href="{{ $file[$key] }}">{{ trans('file.Download') }}</a></td>
			@else
				<td style="border: 1px solid #000; padding: 5px">N/A</td>
			@endif
			<td style="border: 1px solid #000; padding: 5px; text-align: center;">{{$qty[$key].' '.$unit[$key]}}</td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{number_format((float)($total[$key] / $qty[$key]), 2, '.', '')}}</td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$total[$key]}}</td>
		</tr>
		@endforeach
		<tr>
			<td colspan="3" style="border: 1px solid #000; padding: 5px"><strong>Total </strong></td>
			<td style="border: 1px solid #000; padding: 5px; text-align: center;">{{$total_qty}}</td>
			<td style="border: 1px solid #000; padding: 5px"></td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$total_price}}</td>
		</tr>
		<tr>
			<td colspan="5" style="border: 1px solid #000; padding: 5px"><strong>{{ trans('file.Order Tax') }}</strong> </td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$order_tax.'('.$order_tax_rate.'%)'}}</td>
		</tr>
		<tr>
			<td colspan="5" style="border: 1px solid #000; padding: 5px"><strong>{{ trans('file.Order discount') }} </strong> </td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">
				@if($order_discount){{$order_discount}}
				@else 0 @endif
			</td>
		</tr>
		<tr>
			<td colspan="5" style="border: 1px solid #000; padding: 5px"><strong>{{ trans('file.Shipping Cost') }}</strong> </td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">
				@if($shipping_cost){{$shipping_cost}}
				@else 0 @endif
			</td>
		</tr>
		<tr>
			<td colspan="5" style="border: 1px solid #000; padding: 5px"><strong>{{ trans('file.grand total') }}</strong></td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$grand_total}}</td>
		</tr>
		<tr>
			<td colspan="5" style="border: 1px solid #000; padding: 5px"><strong>{{ trans('file.Paid Amount') }}</strong></td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">
				@if($paid_amount){{$paid_amount}}
				@else 0 @endif
			</td>
		</tr>
		<tr>
			<td colspan="5" style="border: 1px solid #000; padding: 5px"><strong>{{ trans('file.Due') }}</strong></td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{number_format((float)($grand_total - $paid_amount), 2, '.', '')}}</td>
		</tr>
	</tbody>
</table>

<p>{{ trans('file.Thank You') }}</p>
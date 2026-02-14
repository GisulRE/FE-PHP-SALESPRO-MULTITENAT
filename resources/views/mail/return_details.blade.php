<h1>{{ trans('file.Return Details') }}</h1>
<p><strong>{{ trans('file.Reference No') }}: </strong>{{$reference_no}}</p>
<h3>{{ trans('file.Order Table') }}</h3>
<table style="border-collapse: collapse; width: 100%;">
	<thead>
		<th style="border: 1px solid #000; padding: 5px">#</th>
		<th style="border: 1px solid #000; padding: 5px">{{ trans('file.product') }}</th>
		<th style="border: 1px solid #000; padding: 5px">{{ trans('file.qty') }}</th>
		<th style="border: 1px solid #000; padding: 5px">{{ trans('file.Unit Price') }}</th>
		<th style="border: 1px solid #000; padding: 5px">SubTotal</th>
	</thead>
	<tbody>
		@foreach($products as $key=>$product)
		<tr>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$key+1}}</td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$product}}</td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$qty[$key].' '.$unit[$key]}}</td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{number_format((float)($total[$key] / $qty[$key]), 2, '.', '')}}</td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$total[$key]}}</td>
		</tr>
		@endforeach
		<tr>
			<td colspan="2" style="border: 1px solid #000; padding: 5px"><strong>Total </strong></td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$total_qty}}</td>
			<td style="border: 1px solid #000; padding: 5px"></td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$total_price}}</td>
		</tr>
		<tr>
			<td colspan="4" style="border: 1px solid #000; padding: 5px"><strong>{{ trans('file.Order Tax') }} </strong> </td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$order_tax.'('.$order_tax_rate.'%)'}}</td>
		</tr>
		<tr>
			<td colspan="4" style="border: 1px solid #000; padding: 5px"><strong>{{ trans('file.grand total') }} </strong></td>
			<td style="border: 1px solid #000; padding: 5px; text-align: end;">{{$grand_total}}</td>
		</tr>
	</tbody>
</table>

<p>{{ trans('file.Thank You') }}</p>
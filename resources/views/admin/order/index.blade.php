@extends('admin.layouts.app')


@section('content')


<div class="container-fluid admin-order-index">


<h3 class="fw-bold mb-4 admin-order-page-title">

<i class="fa-solid fa-cart-shopping"></i>

Manajemen Order

</h3>



<div class="card shadow admin-order-list-card">


<div class="card-body">



<form class="admin-order-filter">

<select name="status"
class="form-select w-auto"
onchange="this.form.submit()">


<option value="">
Semua Status
</option>


@foreach($statusList as $itemStatus)

<option value="{{ $itemStatus }}"
@if($status==$itemStatus)
selected
@endif
>

{{ $itemStatus }}

</option>

@endforeach


</select>


</form>



<th>
Sumber / History
</th>


<div class="table-responsive admin-order-table-wrap">

<table class="table table-hover mt-3 admin-order-table">


<thead>

<tr>

<th>
<td class="admin-order-source-cell">

@php
	$orderSources = [
		'manual' => $order->details->isEmpty(),
		'moogold' => $order->mooGoldOrders->isNotEmpty(),
		'ditusi' => $order->ditusiOrders->isNotEmpty(),
	];

	foreach ($order->details as $detail) {
		$item = $detail->item;

		$usesMooGold = $detail->mooGoldOrder !== null
			|| (
				$item
				&& !empty($item->moogold_category_id)
				&& !empty($item->moogold_variation_id)
			);

		$usesDitusi = $detail->ditusiOrder !== null
			|| (
				$item
				&& (bool) $item->ditusi_enabled
				&& trim((string) $item->ditusi_product_code) !== ''
			);

		$orderSources['moogold'] = $orderSources['moogold'] || $usesMooGold;
		$orderSources['ditusi'] = $orderSources['ditusi'] || $usesDitusi;
		$orderSources['manual'] = $orderSources['manual'] || (!$usesMooGold && !$usesDitusi);
	}
@endphp

<div class="admin-order-source-badges">

	@if($orderSources['manual'])
		<span class="badge admin-order-source-badge manual">Manual</span>
	@endif

	@if($orderSources['moogold'])
		<span class="badge admin-order-source-badge moogold">MooGold</span>
	@endif

	@if($orderSources['ditusi'])
		<span class="badge admin-order-source-badge api">Ditusi API</span>
	@endif

</div>

@foreach($order->mooGoldOrders->take(2) as $history)
	<small class="admin-order-history-line">
		MooGold: {{ $history->moogold_status ?: 'History tercatat' }}
		@if($history->moogold_order_id)
			· #{{ $history->moogold_order_id }}
		@endif
	</small>
@endforeach

@foreach($order->ditusiOrders->take(2) as $history)
	<small class="admin-order-history-line">
		Ditusi: {{ $history->status ?: 'History tercatat' }}
		@if($history->ditusi_transaction_id)
			· #{{ $history->ditusi_transaction_id }}
		@endif
	</small>
@endforeach

@if($orderSources['moogold'] && $order->mooGoldOrders->isEmpty())
	<small class="admin-order-history-line">Belum dikirim ke provider.</small>
@elseif($orderSources['ditusi'] && $order->ditusiOrders->isEmpty())
	<small class="admin-order-history-line">Belum dikirim ke provider.</small>
@endif

</td>
Invoice
</th>

<th>
User
</th>

<th>
Game
</th>

<th>
Payment
</th>

<th>
Total
</th>

<th>
Status
</th>

<th>
Action
</th>

</tr>

</thead>



<tbody>


@foreach($orders as $order)


<tr>


<td>

{{$order->invoice_number}}

</td>



<td>

{{$order->user->name ?? 'Guest'}}

</td>



<td>

{{$order->game->game_name}}

</td>



<td>

{{ $order->payment?->payment_name
	?? ($order->midtrans_payment_type
		? \Illuminate\Support\Str::headline($order->midtrans_payment_type)
		: 'Belum ditentukan') }}

</td>



<td>

Rp {{number_format($order->total_price)}}

</td>




<td>


@if($order->status=='Waiting Payment')

<span class="badge bg-secondary">
Waiting Payment
</span>

@elseif($order->status=='Completed')

<span class="badge bg-success">
Completed
</span>

@elseif($order->status=='Paid')

<span class="badge bg-primary">
Paid
</span>


@elseif($order->status=='Processing')

<span class="badge bg-warning">
Processing
</span>


@elseif($order->status=='Success')

<span class="badge bg-success">
Success
</span>


@elseif($order->status=='Cancelled')

<span class="badge bg-danger">
Canceled
</span>


@endif

</td>




<td>


<a href="{{route('admin.order.show',$order)}}"

class="btn btn-sm btn-primary">

<i class="fa-solid fa-eye"></i>

</a>


@if($order->status=='Paid')


<span class="badge bg-danger ms-1">

Menunggu Konfirmasi

</span>



@endif



</td>


</tr>



@endforeach


</tbody>


</table>

</div>


</div>


</div>



</div>


@endsection
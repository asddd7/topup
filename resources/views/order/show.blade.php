@extends('layouts.app')


@section('content')


<div class="container py-4">

@auth
    <div class="d-flex gap-2">
        <a href="{{ route('order.index') }}"
           class="btn btn-secondary">

            <i class="fa-solid fa-arrow-left me-1"></i>
            Kembali

        </a>
    </div>
@endauth
<div class="card shadow">


<div class="card-header">

Detail Pesanan

</div>



<div class="card-body">


<h5>

{{$order->invoice_number}}

</h5>



<p>

Game :

{{$order->game->game_name}}

</p>



<h5 class="mt-4 mb-3">
    <i class="fa-solid fa-user"></i>
    Data Player
</h5>

<table class="table table-bordered">

@foreach($order->game->player_fields ?? [] as $field)

<tr>

    <th width="220">

        {{ $field['label'] }}

    </th>

    <td>

        {{ $order->player_data[$field['name']] ?? '-' }}

    </td>

</tr>

@endforeach

</table>





<hr>


<h5>

Item

</h5>



@foreach($order->details as $detail)


<div>

{{$detail->item->item_name}}

<br>

Rp {{number_format($detail->subtotal)}}

</div>


@endforeach



<hr>


<h4>

Total :

Rp {{number_format($order->total_price)}}

</h4>



Status:

<span class="badge bg-warning">

{{$order->status}}

</span>


@if($order->status == 'Waiting Payment')


<div class="alert alert-warning mt-3">


<i class="fa-solid fa-triangle-exclamation me-2"></i>


Pembayaran belum selesai atau ditolak admin.


<br>


Silahkan upload bukti pembayaran kembali.


<a href="
{{ route('order.payment',$order->invoice_number) }}
@if($order->guest_token)
?token={{$order->guest_token}}
@endif
"
class="btn btn-success mt-3">

<i class="fa-solid fa-upload me-1"></i>

Upload Bukti Pembayaran

</a>


</div>


@endif

</div>

</div>

</div>

</div>
</div>


@endsection
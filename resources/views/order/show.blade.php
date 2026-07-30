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



{{-- ==========================
     DATA PLAYER
========================== --}}

<h5>
    Data Player
</h5>


<div class="card bg-light border-0">

<div class="card-body">


@if($order->game->player_input_type != 'none')


<div class="mb-3">

<strong>

{{ $order->game->input_label ?? 'Player ID' }}

</strong>

<br>


@if(
in_array(
$order->game->player_input_type,
[
'uid',
'uid_server'
]
)
)

{{ $order->player_uid ?? '-' }}


@elseif(
$order->game->player_input_type == 'riot_id'
)

{{ $order->player_data['riot_id'] ?? '-' }}


@elseif(
$order->game->player_input_type == 'email'
)

{{ $order->player_data['email'] ?? '-' }}


@elseif(
$order->game->player_input_type == 'login'
)

{{ $order->player_data['login_id'] ?? '-' }}


@endif


</div>




@if(
$order->game->input_label_2
)


<div class="mb-3">

<strong>

{{ $order->game->input_label_2 }}

</strong>

<br>


@if(
$order->game->player_input_type == 'uid_server'
)

{{ $order->server_id ?? '-' }}


@elseif(
$order->game->player_input_type == 'riot_id'
)

{{ $order->player_data['tag'] ?? '-' }}


@endif


</div>


@endif




@else


<div class="alert alert-secondary">

Game ini tidak membutuhkan data player.

</div>


@endif



</div>

</div>

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


@endsection
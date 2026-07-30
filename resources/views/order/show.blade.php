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

<h5>Data Player</h5>

@switch($order->game->player_input_type)

    @case('uid')

        <div class="mb-2">
            <strong>UID</strong><br>
            {{ $order->player_uid }}
        </div>

    @break


    @case('uid_server')

        <div class="mb-2">
            <strong>UID</strong><br>
            {{ $order->player_uid }}
        </div>

        <div class="mb-2">
            <strong>Server</strong><br>
            {{ $order->server_id }}
        </div>

    @break


    @case('riot_id')

        <div class="mb-2">
            <strong>Riot ID</strong><br>
            {{ $order->player_data['riot_id'] ?? '-' }}
        </div>

        <div class="mb-2">
            <strong>Tag</strong><br>
            {{ $order->player_data['tag'] ?? '-' }}
        </div>

    @break


    @case('email')

        <div class="mb-2">
            <strong>Email Player</strong><br>
            {{ $order->player_data['email'] ?? '-' }}
        </div>

    @break


    @case('login')

        <div class="mb-2">
            <strong>Login ID</strong><br>
            {{ $order->player_data['login_id'] ?? '-' }}
        </div>

    @break


    @default

        <div class="alert alert-secondary mb-0">
            Game ini tidak memerlukan data player.
        </div>

@endswitch

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
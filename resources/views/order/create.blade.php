@extends('layouts.app')


@section('content')


<div class="container">


<div class="card shadow">


<div class="card-header">

Top Up {{$game->game_name}}

</div>


<div class="card-body">


<form method="POST"
action="{{route('order.store')}}">


@csrf


<input type="hidden"
name="game_id"
value="{{$game->id}}">



<div class="mb-3">

<label>

UID Player

</label>


<input
type="text"
name="uid_player"
class="form-control"
required>

</div>



<div class="mb-3">


<label>

Pilih Nominal

</label>


<select
name="item_id"
class="form-select">


@foreach($items as $item)


<option value="{{$item->id}}">


{{$item->item_name}}

-

Rp {{number_format($item->price)}}


</option>


@endforeach


</select>


</div>



<button class="btn btn-primary w-100">


Buat Pesanan


</button>



</form>


</div>

</div>

</div>


@endsection
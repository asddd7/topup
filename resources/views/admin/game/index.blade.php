@extends('admin.layouts.app')


@section('title','Manajemen Game')


@section('content')


<div class="container-fluid">


<div class="card shadow-sm">


<div class="card-header d-flex justify-content-between align-items-center">


<h5 class="mb-0 fw-bold">

<i class="fa-solid fa-gamepad me-2"></i>

Data Game

</h5>

<button class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#createGameModal">

    <i class="fa-solid fa-plus"></i>

    Tambah Game

</button>

</div>



<div class="card-body">


@if(session('success'))

<div class="alert alert-success">

{{session('success')}}

</div>

@endif



<div class="table-responsive">


<table class="table table-hover align-middle">


<thead>

<tr>

<th>No</th>

<th>Logo</th>

<th>Game</th>

<th>Publisher</th>

<th>Type Input</th>

<th>Status</th>

<th width="150">
Action
</th>

</tr>

</thead>



<tbody>


@foreach($games as $game)


<tr>


<td>
{{$loop->iteration}}
</td>



<td>


@if($game->game_logo)

<img src="{{asset('storage/'.$game->game_logo)}}"
width="60"
class="rounded">


@else

<span class="text-muted">

No Logo

</span>

@endif


</td>



<td>

<strong>

{{$game->game_name}}

</strong>

</td>



<td>

{{$game->publisher ?? '-'}}

</td>

<td>
    {{ $game->player_input_type ?? '-' }}
</td>

<td>


@if($game->is_active)

<span class="badge bg-success">

Aktif

</span>

@else

<span class="badge bg-danger">

Nonaktif

</span>

@endif


</td>



<td>

<a href="{{ route('admin.game.items',$game->id) }}"
class="btn btn-info btn-sm">

    <i class="fa-solid fa-box"></i>

</a>

<button class="btn btn-warning btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#editGameModal{{ $game->id }}">

    <i class="fa-solid fa-pen"></i>

</button>

<form action="{{route('admin.game.destroy',$game->id)}}"
      method="POST"
      class="d-inline">

    @csrf
    @method('DELETE')

    <button class="btn btn-danger btn-sm"
            onclick="return confirm('Hapus game ini?')">

        <i class="fa-solid fa-trash"></i>

    </button>

</form>

</td>


</tr>


@endforeach


</tbody>


</table>


</div>


</div>


</div>


</div>

@include('admin.game.create')

@foreach($games as $game)

@include('admin.game.edit')

@endforeach

@endsection
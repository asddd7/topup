@extends('admin.layouts.app')


@section('title','Manajemen Item')


@section('content')


<div class="container-fluid">


<div class="card shadow-sm">


<div class="card-header d-flex justify-content-between align-items-center">

    <h5 class="fw-bold mb-0">
        <i class="fa-solid fa-box me-2"></i>
        Data Item - {{ $game->game_name }}
    </h5>

    <div class="d-flex gap-2">

        <a href="{{ route('admin.game.index') }}"
           class="btn btn-secondary">

            <i class="fa-solid fa-arrow-left me-1"></i>
            Kembali

        </a>

        <button class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#createItemModal">

            <i class="fa-solid fa-plus me-1"></i>
            Tambah Item

        </button>

    </div>

</div>

<div class="card-body border-bottom bg-light">

    <div class="d-flex align-items-center">

        @if($game->game_logo)

            <img src="{{ asset('storage/'.$game->game_logo) }}"
                 width="60"
                 height="60"
                 class="rounded me-3"
                 style="object-fit:cover;">

        @endif

        <div>

            <h5 class="mb-1">{{ $game->game_name }}</h5>

            <small class="text-muted">
                {{ $game->publisher }}
            </small>

        </div>

    </div>

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

<th>Image</th>

<th>Item</th>

<th>Game</th>

<th>Kategori</th>

<th>Qty</th>

<th>Harga</th>

<th>Stock</th>

<th>Status</th>

<th>Action</th>


</tr>

</thead>



<tbody>


@foreach($items as $item)


<tr>


<td>

{{$loop->iteration}}

</td>



<td>


@if($item->image)


<img src="{{asset('storage/'.$item->image)}}"
width="60"
class="rounded">


@else

<span class="text-muted">
No Image
</span>

@endif


</td>




<td>

<strong>

{{$item->item_name}}

</strong>

<br>

<small class="text-muted">

{{$item->description}}

</small>

</td>




<td>

{{$item->game->game_name}}

</td>




<td>

{{$item->category->category_name}}

</td>




<td>

{{$item->qty}}

</td>




<td>

Rp {{number_format($item->price)}}

</td>




<td>


@if($item->stock > 0)

<span class="badge bg-success">

{{$item->stock}}

</span>


@else


<span class="badge bg-danger">

Habis

</span>


@endif


</td>




<td>


@if($item->is_active)


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


<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#editItemModal{{$item->id}}">


<i class="fa-solid fa-pen"></i>


</button>




<form action="{{route('admin.game.items.destroy',
[
    'game'=>$game->id,
    'item'=>$item->id
])}}"
method="POST"
class="d-inline">


@csrf

@method('DELETE')


<button class="btn btn-danger btn-sm"
onclick="return confirm('Hapus item ini?')">


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

</div>

@include('admin.item.create')

@foreach($items as $item)

@include('admin.item.edit')

@endforeach



@endsection
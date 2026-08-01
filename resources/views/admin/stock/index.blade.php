@extends('admin.layouts.app')

@section('title','Manajemen Stock')

@section('content')

<div class="container-fluid">

<div class="card shadow">

<div class="card-header">

<div class="d-flex justify-content-between align-items-center">

<h5 class="mb-0">

<i class="fa-solid fa-boxes-stacked me-2"></i>

Manajemen Stock

</h5>

</div>

</div>

<div class="card-body">

<form method="GET">

<div class="row g-3 mb-4">

<div class="col-md-4">

<input
type="text"
name="keyword"
class="form-control"
placeholder="Cari item..."
value="{{ request('keyword') }}">

</div>

<div class="col-md-3">

<select
name="game"
class="form-select">

<option value="">Semua Game</option>

@foreach($games as $game)

<option
value="{{$game->id}}"
{{request('game')==$game->id?'selected':''}}>

{{$game->game_name}}

</option>

@endforeach

</select>

</div>

<div class="col-md-3">

<select
name="category"
class="form-select">

<option value="">Semua Kategori</option>

@foreach($categories as $category)

<option
value="{{$category->id}}"
{{request('category')==$category->id?'selected':''}}>

{{$category->category_name}}

</option>

@endforeach

</select>

</div>

<div class="col-md-2 d-grid">

<button class="btn btn-primary">

Cari

</button>

</div>

</div>

</form>

<div class="table-responsive">

<table class="table align-middle">

<thead>

<tr>

<th>Item</th>

<th>Game</th>

<th>Kategori</th>

<th>Stock</th>

<th width="220">

Tambah Stock

</th>

</tr>

</thead>

<tbody>

@foreach($items as $item)

<tr>

<td>

<strong>

{{$item->item_name}}

</strong>

</td>

<td>

{{$item->game->game_name}}

</td>

<td>

{{$item->category->category_name}}

</td>

<td>

@if($item->stock==0)

<span class="badge bg-danger">

{{$item->stock}}

</span>

@elseif($item->stock<20)

<span class="badge bg-warning">

{{$item->stock}}

</span>

@else

<span class="badge bg-success">

{{$item->stock}}

</span>

@endif

</td>

<td>

<form
action="{{route('admin.stock.update',$item)}}"
method="POST">

@csrf

<div class="input-group">

<input
type="number"
name="stock"
min="1"
value="1"
class="form-control">

<button
class="btn btn-success">

Tambah

</button>

</div>

</form>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

{{$items->links()}}

</div>

</div>

</div>

@endsection
@extends('admin.layouts.app')

@section('title','Kategori Item')

@section('content')

<div class="container-fluid">

<div class="card shadow-sm">

<div class="card-header d-flex justify-content-between align-items-center">

<h5 class="fw-bold">

<i class="fa-solid fa-layer-group me-2"></i>

Kategori Item

</h5>

<button class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#createCategoryModal">

    <i class="fa-solid fa-plus"></i>

    Tambah Kategori

</button>

</div>

<div class="card-body">

@if(session('success'))

<div class="alert alert-success">

{{ session('success') }}

</div>

@endif

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th width="70">No</th>

<th>Nama Kategori</th>

<th width="180">Action</th>

</tr>

</thead>

<tbody>

@forelse($categories as $category)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $category->category_name }}</td>

<td>

<button
class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#editCategoryModal{{ $category->id }}">

<i class="fa-solid fa-pen"></i>

</button>

<form
action="{{ route('admin.item-category.destroy',$category->id) }}"
method="POST"
class="d-inline">

@csrf
@method('DELETE')

<button
class="btn btn-danger btn-sm"
onclick="return confirm('Hapus kategori?')">

<i class="fa-solid fa-trash"></i>

</button>

</form>

</td>

</tr>

@empty

<tr>

<td colspan="3" class="text-center">

Belum ada data.

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

</div>

</div>

</div>


@include('admin.item_category.create')

@foreach($categories as $category)

    @include('admin.item_category.edit')

@endforeach


@endsection
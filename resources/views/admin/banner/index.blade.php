@extends('admin.layouts.app')

@section('title','Manajemen Banner')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="fw-bold mb-0">

                <i class="fa-solid fa-images me-2"></i>

                Data Banner

            </h5>

            <button
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#createBannerModal">

                <i class="fa-solid fa-plus me-1"></i>

                Tambah Banner

            </button>

        </div>

        <div class="card-body">

            @if(session('success'))

                <div class="alert alert-success">

                    {{ session('success') }}

                </div>

            @endif

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                    <tr>

                        <th width="60">No</th>

                        <th width="220">Banner</th>

                        <th>Judul</th>

                        <th>Game</th>

                        <th>Link</th>

                        <th width="90">Urutan</th>

                        <th width="90">Status</th>

                        <th width="150">Action</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($banners as $banner)

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                @if($banner->image)

                                    <img
                                        src="{{ asset('storage/'.$banner->image) }}"
                                        class="banner-thumb">

                                @else

                                    <div class="banner-thumb bg-light d-flex align-items-center justify-content-center">

                                        No Image

                                    </div>

                                @endif

                            </td>

                            <td>

                                <strong>

                                    {{ $banner->title }}

                                </strong>

                                @if($banner->description)

                                    <br>

                                    <small class="text-muted">

                                        {{ Str::limit($banner->description,60) }}

                                    </small>

                                @endif

                            </td>

                            <td>

                                {{ $banner->game->game_name ?? 'Semua Game' }}

                            </td>

                            <td>

                                @if($banner->link)

                                    <a
                                        href="{{ $banner->link }}"
                                        target="_blank">

                                        {{ Str::limit($banner->link,30) }}

                                    </a>

                                @else

                                    -

                                @endif

                            </td>

                            <td>

                                {{ $banner->sort_order }}

                            </td>

                            <td>

                                @if($banner->is_active)

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

                                <button
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editBannerModal{{ $banner->id }}">

                                    <i class="fa-solid fa-pen"></i>

                                </button>

                                <form
                                    action="{{ route('admin.banner.destroy',$banner->id) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus banner ini?')">

                                        <i class="fa-solid fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center text-muted py-5">

                                Belum ada banner.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@include('admin.banner.create')

@foreach($banners as $banner)

@include('admin.banner.edit')

@endforeach

@endsection
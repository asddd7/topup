@extends('admin.layouts.app')

@section('title', 'Pengaturan Top Seller')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Pengaturan Top Seller
            </h3>

            <p class="text-muted mb-0">
                Pilih item yang ingin ditampilkan pada bagian Top Seller.
            </p>

        </div>

    </div>
    <a href="{{ route('admin.dashboard') }}"
        class="btn btn-outline-secondary">

        <i class="fa-solid fa-arrow-left me-1"></i>
        Kembali

    </a>

    @if(session('success'))

        <div class="alert alert-success">

            <i class="fa-solid fa-circle-check me-1"></i>

            {{ session('success') }}

        </div>

    @endif


    <form
        method="POST"
        action="{{ route('top-seller.update') }}"
    >

        @csrf


        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <strong>
                        Daftar Item
                    </strong>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-save me-1"></i>

                        Simpan Top Seller

                    </button>

                </div>

            </div>


            <div class="card-body">

                <div class="row g-3">

                    @forelse($items as $item)

                        <div class="col-md-6 col-lg-4">

                            <label
                                class="card h-100 border shadow-sm"
                                style="cursor:pointer;"
                            >

                                <div class="card-body">

                                    <div class="d-flex align-items-center gap-3">

                                        {{-- IMAGE --}}

                                        @if($item->image)

                                            <img
                                                src="{{ asset('storage/' . $item->image) }}"
                                                alt="{{ $item->item_name }}"
                                                width="60"
                                                height="60"
                                                class="rounded object-fit-cover"
                                            >

                                        @else

                                            <div
                                                class="bg-light rounded d-flex align-items-center justify-content-center"
                                                style="width:60px;height:60px;"
                                            >

                                                <i class="fa-solid fa-box text-muted"></i>

                                            </div>

                                        @endif


                                        {{-- INFO --}}

                                        <div class="flex-grow-1">

                                            <div class="fw-bold">

                                                {{ $item->item_name }}

                                            </div>

                                            <small class="text-muted">

                                                {{ $item->game->game_name ?? '-' }}

                                            </small>

                                            <div class="mt-1">

                                                <small class="fw-semibold text-primary">

                                                    Rp {{ number_format($item->price) }}

                                                </small>

                                            </div>

                                        </div>


                                        {{-- CHECKBOX --}}

                                        <div>

                                            <input
                                                type="checkbox"
                                                name="top_seller[]"
                                                value="{{ $item->id }}"
                                                class="form-check-input"
                                                style="width:20px;height:20px;"
                                                {{ $item->top_seller ? 'checked' : '' }}
                                            >

                                        </div>

                                    </div>

                                </div>

                            </label>

                        </div>

                    @empty

                        <div class="col-12">

                            <div class="text-center py-5 text-muted">

                                <i class="fa-solid fa-box-open fa-2x mb-3"></i>

                                <p class="mb-0">
                                    Belum ada item.
                                </p>

                            </div>

                        </div>

                    @endforelse

                </div>

            </div>


            <div class="card-footer bg-white text-end">

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="fa-solid fa-save me-1"></i>

                    Simpan Perubahan

                </button>

            </div>

        </div>

    </form>

</div>

@endsection
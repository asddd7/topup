@extends('admin.layouts.app')


@section('title','Metode Pembayaran')


@section('content')


<div class="container-fluid admin-payment-page">


<div class="card shadow-sm">


<div class="card-header d-flex justify-content-between align-items-center gap-3">


<h5 class="fw-bold">

<i class="fa-solid fa-credit-card me-2"></i>

Metode Pembayaran

</h5>



<div class="d-flex flex-wrap gap-2">
<form action="{{ route('admin.payment.sync-midtrans') }}" method="POST">
@csrf
<button class="btn btn-outline-primary" type="submit">
<i class="fa-solid fa-arrows-rotate"></i>
Sinkron Midtrans
</button>
</form>

<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#createPaymentModal">


<i class="fa-solid fa-plus"></i>

Tambah Payment


</button>
</div>


</div>




<div class="card-body">


@if(session('success'))

<div class="alert alert-success">

{{session('success')}}

</div>

@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="alert {{ $midtransError ? 'alert-warning' : 'alert-info' }} d-flex justify-content-between align-items-center gap-3">
	<div>
		<strong><i class="fa-solid fa-cloud me-1"></i> Midtrans</strong>
		<div class="small">
			{{ $midtransError ?? count($midtransChannels) . ' channel aktif tersedia dari merchant preferences Midtrans.' }}
		</div>
	</div>
	@if(!$midtransError)
		<div class="d-flex flex-wrap gap-1 justify-content-end">
			@foreach($midtransChannels as $channel)
				<span class="badge bg-primary">{{ $channel['name'] }}</span>
			@endforeach
		</div>
	@endif
</div>



<div class="table-responsive">


<table class="table table-hover">


<thead>

<tr>

<th>No</th>

<th>Image</th>

<th>Nama</th>

<th>Nomor</th>

<th>Pemilik</th>

<th>Tipe</th>

<th>Status</th>

<th>Aksi</th>

</tr>

</thead>



<tbody>


@foreach($payments as $payment)


<tr>


<td>
{{$loop->iteration}}
</td>


<td>


@if($payment->image)

<img src="{{asset('storage/'.$payment->image)}}"
width="60"
class="rounded">


@else

<i class="fa-solid fa-image text-muted"></i>

@endif


</td>



<td>

{{$payment->payment_name}}

</td>


<td>

{{$payment->payment_number}}

</td>



<td>

{{$payment->account_name}}

</td>



<td>

{{$payment->payment_type}}

</td>



<td>

@if($payment->is_active)

<span class="payment-status-badge badge bg-success">
Aktif
</span>

@else

<span class="payment-status-badge badge bg-danger">
Nonaktif
</span>

@endif

</td>



<td>

<form
action="{{ route('admin.payment.toggle-active', $payment) }}"
method="POST"
class="payment-toggle-form d-inline-flex align-items-center gap-2"
>
@csrf
@method('PATCH')

<button
type="submit"
class="payment-status-toggle {{ $payment->is_active ? 'is-active' : '' }}"
role="switch"
aria-checked="{{ $payment->is_active ? 'true' : 'false' }}"
title="{{ $payment->is_active ? 'Nonaktifkan' : 'Aktifkan' }} payment"
>
<span class="payment-status-toggle-track">
<span class="payment-status-toggle-thumb"></span>
</span>
<span class="visually-hidden">
{{ $payment->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
</span>
</button>

</form>


<button
class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#editPaymentModal{{$payment->id}}">

<i class="fa-solid fa-pen"></i>


</button>



<form
action="{{route('admin.payment.destroy',$payment->id)}}"
method="POST"
class="d-inline">


@csrf
@method('DELETE')


<button
class="btn btn-danger btn-sm"
onclick="return confirm('Hapus payment?')">


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


@include('admin.payment.create')


@foreach($payments as $payment)

@include('admin.payment.edit')

@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.payment-toggle-form').forEach(function (form) {
		form.addEventListener('submit', async function (event) {
			event.preventDefault();

			const button = form.querySelector('.payment-status-toggle');
			const row = form.closest('tr');
			const badge = row?.querySelector('.payment-status-badge');
			const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

			if (!button || button.classList.contains('is-changing')) {
				return;
			}

			button.classList.add('is-changing');

			try {
				const response = await fetch(form.action, {
					method: 'PATCH',
					headers: {
						Accept: 'application/json',
						'X-CSRF-TOKEN': csrfToken,
						'X-Requested-With': 'XMLHttpRequest',
					},
					credentials: 'same-origin',
				});

				const result = await response.json();

				if (!response.ok || !result.success) {
					throw new Error(result.message || 'Status payment gagal diubah.');
				}

				const isActive = Boolean(result.is_active);
				button.classList.toggle('is-active', isActive);
				button.setAttribute('aria-checked', isActive ? 'true' : 'false');
				button.title = isActive ? 'Nonaktifkan payment' : 'Aktifkan payment';
				button.querySelector('.visually-hidden').textContent =
					isActive ? 'Nonaktifkan' : 'Aktifkan';

				if (badge) {
					badge.classList.toggle('bg-success', isActive);
					badge.classList.toggle('bg-danger', !isActive);
					badge.textContent = isActive ? 'Aktif' : 'Nonaktif';
				}
			} catch (error) {
				window.Swal
					? Swal.fire('Gagal', error.message, 'error')
					: alert(error.message);
			} finally {
				window.setTimeout(function () {
					button.classList.remove('is-changing');
				}, 220);
			}
		});
	});
});
</script>
@endpush


@endsection
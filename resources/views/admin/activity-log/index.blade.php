@extends('admin.layouts.app')

@section('title','Log Aktifitas')

@section('content')

@php
use Illuminate\Support\Str;
@endphp
<table class="table table-hover">

<thead>

<tr>

<th>Waktu</th>

<th>User</th>

<th>Module</th>

<th>Aktivitas</th>

<th>IP</th>

<th>Browser</th>

<th width="120">Aksi</th>

</tr>

</thead>

<tbody>

@foreach($logs as $log)

<tr>

<td>

{{ $log->created_at->format('d M Y H:i') }}

</td>

<td>

{{ $log->user->name ?? 'Guest' }}

</td>

<td>

{{ $log->module }}

</td>

<td>

{{ $log->activity }}

</td>

<td>

{{ $log->ip_address }}

</td>

<td>

{{ Str::limit($log->user_agent,40) }}

</td>

<td>
    <button
        class="btn btn-sm btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#logModal{{ $log->id }}">

        <i class="fa-solid fa-eye"></i>
        Detail

    </button>
</td>

</tr>

@endforeach

</tbody>

</table>

{{ $logs->links() }}

@foreach($logs as $log)

@include('admin.activity-log.show')

@endforeach
@endsection
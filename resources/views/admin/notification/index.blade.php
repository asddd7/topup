@extends('admin.layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-bell me-2"></i>Notifikasi</h3>
            <p class="text-muted mb-0">Pantau peringatan order dan stok dari panel admin.</p>
        </div>

        @if(($notificationCount ?? 0) > 0)
            <form action="{{ route('admin.notification.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fa-solid fa-check-double me-1"></i>Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @forelse($notifications as $notification)
                <div class="d-flex flex-wrap align-items-start gap-3 p-3 border-bottom {{ $notification->is_read ? '' : 'bg-light' }}">
                    <div class="text-primary fs-4 pt-1">
                        <i class="fa-solid {{ $notification->item_id ? 'fa-boxes-stacked' : 'fa-receipt' }}"></i>
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <strong>{{ $notification->title }}</strong>
                            @unless($notification->is_read)
                                <span class="badge bg-primary">Baru</span>
                            @endunless
                        </div>
                        <div class="text-muted">{{ $notification->message }}</div>
                        <small class="text-secondary">{{ $notification->created_at?->diffForHumans() }}</small>
                    </div>

                    <div class="d-flex gap-2 ms-auto">
                        @if($notification->order_id || $notification->item_id)
                            <form action="{{ route('admin.notification.read', $notification) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-primary" type="submit" title="Buka sumber">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.notification.toggle-read', $notification) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-outline-secondary" type="submit" title="{{ $notification->is_read ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' }}">
                                <i class="fa-solid {{ $notification->is_read ? 'fa-envelope' : 'fa-envelope-open' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.notification.destroy', $notification) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fa-regular fa-bell-slash fs-2 mb-2"></i>
                    <div>Belum ada notifikasi.</div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
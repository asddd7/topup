<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends BaseAdminController
{
    public function index(Request $request)
    {
        $notifications = Notification::with(['order', 'item'])
            ->where('user_id', $request->user()->id)
            ->when($request->boolean('unread'), fn ($query) => $query->unread())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.notification.index', compact('notifications'));
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    public function read(Request $request, Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);

        $notification->markAsRead();

        if ($notification->order_id) {
            return redirect()->route('admin.order.show', $notification->order_id);
        }

        if ($notification->item_id) {
            return redirect()->route('admin.stock.index');
        }

        return redirect()->route('admin.notification.index');
    }

    public function toggleRead(Request $request, Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);

        $notification->is_read
            ? $notification->markAsUnread()
            : $notification->markAsRead();

        return back()->with('success', 'Status notifikasi diperbarui.');
    }

    public function destroy(Request $request, Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);

        $notification->delete();

        return back()->with('success', 'Notifikasi telah dihapus.');
    }
}

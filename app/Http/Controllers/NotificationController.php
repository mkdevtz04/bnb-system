<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Both dashboards rendered an unread-notification badge with nothing anywhere to
 * clear it, so the count only ever grew.
 */
class NotificationController extends Controller
{
    public function read(Request $request, string $id)
    {
        $notification = $request->user()->unreadNotifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return $url ? redirect()->to($url) : back();
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All caught up.');
    }
}

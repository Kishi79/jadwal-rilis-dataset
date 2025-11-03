<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Tandai semua notifikasi yang belum dibaca sebagai telah dibaca.
     */
    public function markAsRead()
    {
        // Ambil user yang sedang login
        $user = auth()->user();

        if ($user) {
            // Tandai semua notifikasinya yang belum dibaca
            $user->unreadNotifications->markAsRead();
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error'], 403);
    }
}
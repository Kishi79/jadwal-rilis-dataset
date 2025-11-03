<?php

// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth; // Pastikan ini ada
use Illuminate\Support\Facades\View; // Pastikan ini ada

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $allNotifications = collect();
            $unreadNotificationCount = 0;

            if (Auth::check() && Auth::user()->isAdmin()) {
                // Ambil 15 notifikasi TERBARU sebagai riwayat
                $allNotifications = Auth::user()->notifications()->take(15)->get();
                
                // Ambil HANYA JUMLAH notifikasi yang belum dibaca
                $unreadNotificationCount = Auth::user()->unreadNotifications->count();
            }

            // Kirim kedua variabel ini ke view
            $view->with('allNotifications', $allNotifications)
                 ->with('unreadNotificationCount', $unreadNotificationCount);
        });
    }
}
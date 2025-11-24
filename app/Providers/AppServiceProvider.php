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

            // CUKUP CEK LOGIN SAJA (Hapus && Auth::user()->isAdmin())
            if (Auth::check()) {
                // Ambil notifikasi milik user yang sedang login (Admin ATAU OPD)
                $allNotifications = Auth::user()->notifications()->take(15)->get();
                $unreadNotificationCount = Auth::user()->unreadNotifications->count();
            }

            $view->with('allNotifications', $allNotifications)
                ->with('unreadNotificationCount', $unreadNotificationCount);
        });
    }
}

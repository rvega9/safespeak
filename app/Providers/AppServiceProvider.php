<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\Response; // Ensure this matches your Message model name

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
        // Share unreadCount globally across all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $userId = Auth::id();

                // Logic for Students: Count unread responses from Guidance
                $myReportIds = Report::where('user_id', $userId)->pluck('report_id');
                
                $unreadCount = Response::whereIn('report_id', $myReportIds)
                                      ->where('user_id', '!=', $userId)
                                      ->where('is_read', false)
                                      ->count();

                $view->with('globalUnreadCount', $unreadCount);
            }
        });
    }
}
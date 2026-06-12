<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Profile;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Carbon::setLocale('id');

        View::composer('*', function ($view) {
            if (Auth::check()) {
                $view->with('authProfile', Profile::find(Auth::id()));
            }
        });
    }
}

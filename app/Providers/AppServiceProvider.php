<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });
    }
}
<?php

namespace App\Providers;

use App\Models\Prestacion;
use App\Observers\PrestacionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Prestacion::observe(PrestacionObserver::class);
    }
}

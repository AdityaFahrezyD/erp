<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Http\Responses\Auth\LogoutResponse;
use App\Http\Responses\LoginResponses;
use App\Http\Responses\LogoutResponses;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, LoginResponses::class);
        $this->app->singleton(LogoutResponse::class, LogoutResponses::class);
    }
    // public $singletons = [
    //     \Filament\Http\Responses\Auth\Contracts\LoginResponse::class => \App\Http\Responses\LoginResponses::class,
    //     \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class => \App\Http\Responses\LogoutResponses::class,
    // ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
    }
}

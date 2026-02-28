<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Contracts\AuthInterface;
use App\Http\Contracts\BaseInterface;
use App\Http\Contracts\UserInterface;
use App\Http\Repositories\BaseRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Services\AuthService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthInterface::class, AuthService::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(BaseInterface::class, BaseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}

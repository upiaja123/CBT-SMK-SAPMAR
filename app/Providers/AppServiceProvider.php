<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('path.public', function() {
            // Jika dideploy di Hostinger (cbt-app dan public_html terpisah)
            $hostingerPath = base_path('../public_html');
            if (is_dir($hostingerPath)) {
                return $hostingerPath;
            }
            
            // Jika di local (Laragon)
            return base_path('public');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant 'super_admin' role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        Gate::define('viewLogViewer', function ($user = null) {
            return $user && $user->hasRole('super_admin');
        });
    }
}

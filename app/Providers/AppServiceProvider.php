<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Modul 2 & 3: Mode ketat di lingkungan lokal
        Model::preventLazyLoading(! app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        // Modul 6: Gate untuk kontrol akses berbasis fungsi (anti-BFLA)
        Gate::define('kelola-master-kamar', function (User $user): bool {
            return $user->hasRole('admin');
        });

        Gate::define('operasional-frontdesk', function (User $user): bool {
            return $user->hasAnyRole(['admin', 'resepsionis']);
        });
    }
}
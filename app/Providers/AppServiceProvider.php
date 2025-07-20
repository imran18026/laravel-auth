<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        //
    }
}



// namespace App\Providers;

// use App\Models\User;
// use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// use Illuminate\Support\Facades\Gate;

// class AuthServiceProvider extends ServiceProvider
// {
//     protected $policies = [
//         // 'App\Models\Model' => 'App\Policies\ModelPolicy',
//     ];

//     public function boot(): void
//     {
//         $this->registerPolicies();

//         Gate::define('admin-access', fn(User $user) => $user->isAdmin());
//         Gate::define('super-admin-access', fn(User $user) => $user->isSuperAdmin());
//     }
// }

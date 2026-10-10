<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserCanSignIn;
use App\Http\Middleware\EnsureUserHasRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

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
        // Re-run role and account checks on every Livewire action, not only on the first page load.
        Livewire::addPersistentMiddleware([
            EnsureUserHasRole::class,
            EnsureUserCanSignIn::class,
        ]);

        Password::defaults(fn () => Password::min(8));

        // Price list, portfolio and term templates are managed by studio admins. Records are already limited
        // to the user's studio by the BelongsToStudio scope.
        Gate::define('manage-studio-content', fn (User $user): bool => $user->isStudioAdmin());

        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}

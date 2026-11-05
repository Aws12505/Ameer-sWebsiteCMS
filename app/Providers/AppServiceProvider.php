<?php

namespace App\Providers;

use App\Models\User;
use App\Support\FrontendRevalidator;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(FrontendRevalidator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureCms();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure the CMS: roles, the public API and front-end revalidation.
     */
    protected function configureCms(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        JsonResource::withoutWrapping();

        if (filled(config('cms.trusted_proxies'))) {
            TrustProxies::at(config('cms.trusted_proxies'));
        }

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(config('cms.contact.max_per_hour_per_ip'))
                ->by($request->ip())
                ->response(fn () => response()->json([
                    'ok' => false,
                    'error' => 'Too many requests. Please call us or try again later.',
                ]));
        });

        $this->app->terminating(fn () => $this->app->make(FrontendRevalidator::class)->flush());
    }
}

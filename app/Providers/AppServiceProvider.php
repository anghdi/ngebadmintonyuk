<?php

namespace App\Providers;

use App\Contracts\PushNotificationSender;
use App\Models\PushNotification;
use App\Models\User;
use App\Services\PushNotificationManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View as LaravelView;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PushNotificationSender::class, PushNotificationManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());
        View::composer('layouts.app', function (LaravelView $view): void {
            $user = auth()->user();
            $notifications = PushNotification::query()
                ->where('success_count', '>', 0)
                ->when(! $user->isAdmin(), fn ($query) => $query->where(function ($query) use ($user): void {
                    $query->where('audience', 'all')
                        ->orWhere(function ($query) use ($user): void {
                            $query->where('audience', 'session')
                                ->whereHas('playSession.registrations', fn ($registrations) => $registrations->whereBelongsTo($user));
                        });
                }))
                ->select(['id', 'title', 'body', 'url', 'created_at'])
                ->latest()
                ->limit(8)
                ->get();

            $view->with('headerNotifications', $notifications);
        });
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
}

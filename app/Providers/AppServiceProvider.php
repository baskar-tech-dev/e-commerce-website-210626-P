<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CategoryRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\TagRepositoryInterface;
use App\Repositories\TagRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(\App\Repositories\ProductRepositoryInterface::class, \App\Repositories\ProductRepository::class);
        $this->app->bind(\App\Repositories\InventoryRepositoryInterface::class, \App\Repositories\InventoryRepository::class);
        $this->app->bind(\App\Repositories\PurchaseOrderRepositoryInterface::class, \App\Repositories\PurchaseOrderRepository::class);
        $this->app->bind(\App\Repositories\CustomerRepositoryInterface::class, \App\Repositories\CustomerRepository::class);
        $this->app->bind(\App\Repositories\PaymentRepositoryInterface::class, \App\Repositories\PaymentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Subscribe payment activity listener
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\LogPaymentActivity::class);

        // Force HTTPS in production mode
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Rate Limiting Configurations
        RateLimiter::for('public_api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('login_api', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . '|' . $request->input('email'));
        });

        RateLimiter::for('checkout_api', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('payment_api', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('coupon_api', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('authenticated_api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('admin_api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Audit log authentication events securely
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                \App\Models\AuditLog::create([
                    'user_id' => $event->user->id,
                    'auditable_type' => get_class($event->user),
                    'auditable_id' => $event->user->id,
                    'action' => 'login',
                    'old_values' => null,
                    'new_values' => ['email' => $event->user->email],
                    'ip_address' => request()->ip(),
                    'user_agent' => substr(request()->userAgent(), 0, 255),
                ]);
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            function ($event) {
                if ($event->user) {
                    \App\Models\AuditLog::create([
                        'user_id' => $event->user->id,
                        'auditable_type' => get_class($event->user),
                        'auditable_id' => $event->user->id,
                        'action' => 'logout',
                        'old_values' => null,
                        'new_values' => ['email' => $event->user->email],
                        'ip_address' => request()->ip(),
                        'user_agent' => substr(request()->userAgent(), 0, 255),
                    ]);
                }
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            function ($event) {
                \App\Models\AuditLog::create([
                    'user_id' => null,
                    'auditable_type' => \App\Models\User::class,
                    'auditable_id' => null,
                    'action' => 'login_failed',
                    'old_values' => null,
                    'new_values' => ['credentials_email' => $event->credentials['email'] ?? '[NOT PROVIDED]'],
                    'ip_address' => request()->ip(),
                    'user_agent' => substr(request()->userAgent(), 0, 255),
                ]);
            }
        );
    }
}

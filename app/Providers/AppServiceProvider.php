<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CategoryRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\BrandRepositoryInterface;
use App\Repositories\BrandRepository;
use App\Repositories\TagRepositoryInterface;
use App\Repositories\TagRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(\App\Repositories\ProductRepositoryInterface::class, \App\Repositories\ProductRepository::class);
        $this->app->bind(\App\Repositories\InventoryRepositoryInterface::class, \App\Repositories\InventoryRepository::class);
        $this->app->bind(\App\Repositories\PurchaseOrderRepositoryInterface::class, \App\Repositories\PurchaseOrderRepository::class);
        $this->app->bind(\App\Repositories\CustomerRepositoryInterface::class, \App\Repositories\CustomerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

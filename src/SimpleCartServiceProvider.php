<?php

namespace Fwcloud916\SimpleCart;

use Illuminate\Support\ServiceProvider;

class SimpleCartServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/simple-cart.php', 'simple-cart');

        // Register the service the package provides.
        $this->app->singleton('simple-cart', function ($app) {
            return new SimpleCart();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['simple-cart'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/simple-cart.php' => config_path('simple-cart.php'),
        ], 'simple-cart.config');

        // Export the migration
        if (! class_exists('CreateSimpleCouponsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_simple_coupons_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_simple_coupons_table.php'),
                // you can add any number of migrations here
            ], 'migrations');
        }

        if (! class_exists('CreateSimpleProductsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_simple_products_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_simple_products_table.php'),
                // you can add any number of migrations here
            ], 'migrations');
        }

        if (! class_exists('CreateSimpleCartsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_simple_carts_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_simple_carts_table.php'),
                // you can add any number of migrations here
            ], 'migrations');
        }
    }
}

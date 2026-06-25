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
        // Display qty without trailing zeros: 18.750 → "18.75", 18.000 → "18"
        \Blade::directive('qty', function ($expression) {
            return "<?php echo rtrim(rtrim(number_format((float)($expression), 3, '.', ''), '0'), '.'); ?>";
        });
    }
}

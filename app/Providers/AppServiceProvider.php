<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);

        Blade::directive('idr', function (string $expression): string {
            return "<?php echo 'Rp&nbsp;'.number_format((float) ({$expression}), 0, ',', '.'); ?>";
        });
    }
}

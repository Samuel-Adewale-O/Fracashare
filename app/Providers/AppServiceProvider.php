<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Share current locale with all views
        View::composer('*', function ($view) {
            $view->with('currentLocale', Session::get('locale', config('languages.default')));
            $view->with('supportedLocales', config('languages.supported'));
        });

        // Custom Blade directive for translations with fallback
        Blade::directive('t', function ($expression) {
            return "<?php echo trans($expression) ?? $expression; ?>";
        });

        // Money formatting directive
        Blade::directive('money', function ($amount) {
            return "<?php echo '₦' . number_format($amount, 2); ?>";
        });
    }
}
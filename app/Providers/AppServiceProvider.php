<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(Cart::class, fn ($app) => new Cart($app['session.store']));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');

        Blade::directive('inr', fn (string $expression) => "<?php echo \App\Support\Money::inr($expression); ?>");

        View::composer(['layouts.app'], function ($view): void {
            $view->with('cartCount', app(Cart::class)->count());
            $view->with('navCategories', Category::orderBy('sort_order')->get(['name', 'slug']));
        });
    }
}

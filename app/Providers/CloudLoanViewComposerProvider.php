<?php

namespace App\Providers;

use CloudLoan\ViewComposers\AuthUserDashboardComposer;
use CloudLoan\ViewComposers\SidebarNavigationLinksViewComposer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;

class CloudLoanViewComposerProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(['dashboard.*', 'partials.*'], AuthUserDashboardComposer::class);
        view()->composer(['partials._menu_bar'], SidebarNavigationLinksViewComposer::class);
        view()->composer('*', function (View $view) {
           return $view->with('currency', config('app.currency'));
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

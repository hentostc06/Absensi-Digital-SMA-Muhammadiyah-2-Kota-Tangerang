<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
class AppServiceProvider extends ServiceProvider {
    public function register(): void {}
    public function boot(): void { Paginator::useBootstrapFive(); Date::setLocale('id'); }
}

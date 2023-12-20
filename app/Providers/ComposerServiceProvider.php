<?php namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * return void
     */
    public function boot()
    {
        View::composer('*', 'App\Http\ViewComposers\GenreComposer');
        View::composer('*', 'App\Http\ViewComposers\WeekComposer');
    }
}

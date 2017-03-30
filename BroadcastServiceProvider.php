<?php namespace PedroGaldino\EchoBroadcastDriver;

use Illuminate\Support\ServiceProvider;
use Illuminate\Broadcasting\BroadcastManager;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        app(BroadcastManager::class)->extend('echo', function ($app) {
            $connection = $this->app['config']["broadcasting.connections.echo"]['connection'];
            return new EchoBroadcaster($connection);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        //
    }
}
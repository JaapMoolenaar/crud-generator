<?php

namespace Appzcoder\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use Appzcoder\CrudGenerator\View\Factory as ViewFactory;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/crudgenerator.php' => config_path('crudgenerator.php'),
        ]);

        $this->publishes([
            __DIR__ . '/stubs/' => base_path('resources/crud-generator/'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(
            'Appzcoder\CrudGenerator\Commands\CrudCommand',
            'Appzcoder\CrudGenerator\Commands\CrudControllerCommand',
            'Appzcoder\CrudGenerator\Commands\CrudModelCommand',
            'Appzcoder\CrudGenerator\Commands\CrudMigrationCommand',
            'Appzcoder\CrudGenerator\Commands\CrudViewCommand'
        );
        
        // Register the BladeStringViewFactory which allows us to
        // parse a blade template passed as string (bascially what the blade 
        // engine does by reading a file and then parsing it)
        $this->app->singleton('BladeStringViewFactory', function($app)
        {
            $resolver = $app['view.engine.resolver'];
            $finder = $app['view.finder'];
            $events = $app['events'];

            $env = new ViewFactory($resolver, $finder, $events);

            $env->setContainer($app);
            $env->share('app', $app);

            return $env;
        });
    }

}

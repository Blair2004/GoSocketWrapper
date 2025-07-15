<?php

namespace GoSocket\Wrapper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use GoSocket\Wrapper\Console\Commands\MakeHandlerCommand;
use GoSocket\Wrapper\Console\Commands\SocketHandlerCommand;
use GoSocket\Wrapper\Console\Commands\ListHandlersCommand;
use GoSocket\Wrapper\Listeners\EventListener;
use GoSocket\Wrapper\Listeners\UserLoginListener;
use GoSocket\Wrapper\Services\HandlerDiscovery;
use GoSocket\Wrapper\Services\SocketHttpClient;

class GoSocketServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gosocket.php', 'gosocket');
        
        // Register services
        $this->app->singleton('gosocket.client', function () {
            return new SocketHttpClient();
        });
        
        $this->app->singleton(HandlerDiscovery::class, function () {
            return new HandlerDiscovery();
        });
        
        // Register custom action handlers collection
        $this->app->singleton('gosocket.handlers', function () {
            $handlers = collect();
            
            // Auto-register package handlers
            $packageHandlersPath = __DIR__ . '/Handlers';
            if (is_dir($packageHandlersPath)) {
                $handlerFiles = glob($packageHandlersPath . '/*.php');
                foreach ($handlerFiles as $file) {
                    $className = 'GoSocket\\Wrapper\\Handlers\\' . basename($file, '.php');
                    if (class_exists($className)) {
                        $reflection = new \ReflectionClass($className);
                        // Skip abstract classes
                        if (!$reflection->isAbstract()) {
                            $handlers->push($className);
                        }
                    }
                }
            }
            
            return $handlers;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'gosocket');
        
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeHandlerCommand::class,
                SocketHandlerCommand::class,
                ListHandlersCommand::class,
            ]);
        }

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/gosocket.php' => config_path('gosocket.php'),
        ], 'gosocket-config');

        // Publish assets
        $this->publishes([
            __DIR__ . '/../resources/js' => public_path('vendor/gosocket'),
        ], 'gosocket-assets');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/gosocket'),
        ], 'gosocket-views');

        // Register Blade directives
        $this->registerBladeDirectives();
        
        // Register event listeners
        $this->registerEventListeners();
    }

    /**
     * Register Blade directives for socket client
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('socketClient', function () {
            return "<?php echo view('gosocket::client'); ?>";
        });
    }

    /**
     * Register event listeners
     */
    protected function registerEventListeners(): void
    {
        // Listen for all events to broadcast socket events
        Event::listen('*', EventListener::class);
        
        // Listen for login events to generate JWT
        Event::listen('Illuminate\Auth\Events\Login', UserLoginListener::class);
    }
}

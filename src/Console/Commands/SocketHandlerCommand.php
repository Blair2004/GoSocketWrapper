<?php

namespace GoSocket\Wrapper\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use GoSocket\Wrapper\Services\HandlerDiscovery;

class SocketHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket:handle {--payload= : Path to the JSON payload file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle socket messages from GoSocket server';

    /**
     * Handler discovery service
     *
     * @var HandlerDiscovery
     */
    protected $handlerDiscovery;

    /**
     * Create a new command instance.
     *
     * @param HandlerDiscovery $handlerDiscovery
     */
    public function __construct(HandlerDiscovery $handlerDiscovery)
    {
        parent::__construct();
        $this->handlerDiscovery = $handlerDiscovery;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $payloadPath = $this->option('payload');

        if (!$payloadPath || !file_exists($payloadPath)) {
            $this->error('Payload file not found or not provided.');
            return 1;
        }

        try {
            $payload = json_decode(file_get_contents($payloadPath), true);

            if (!is_array($payload) || !isset($payload['action'], $payload['data'], $payload['auth'])) {
                $this->error('Invalid JSON payload structure.');
                return 1;
            }

            // Find the action handler            $handlerClass = $this->handlerDiscovery->findHandler($payload['action']);
            
            if (!$handlerClass) {
                $this->error(sprintf('No handler found for: %s', $payload['action']));
                return 1;
            }

            $handler = new $handlerClass();
            
            // Get handler-specific middleware and merge with global ones
            $handlerMiddleware = method_exists($handler, 'middlewares') ? $handler->middlewares() : [];
            $globalMiddleware = config('gosocket.middlewares', []);
            
            // Combine and deduplicate middleware
            $middlewares = array_unique(array_merge($globalMiddleware, $handlerMiddleware));

            // Process payload through middleware pipeline
            $processedPayload = app(Pipeline::class)
                ->send($payload)
                ->through($this->resolveMiddlewares($middlewares))
                ->then(function ($payload) {
                    return $payload;
                });

            // Execute the handler
            $handler->handle($processedPayload);

            $this->info('Socket handler processed successfully.');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error processing socket handler: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Resolve middleware instances from configuration
     *
     * @param array $middlewares
     * @return array
     */
    protected function resolveMiddlewares(array $middlewares): array
    {
        return collect($middlewares)->map(function ($middleware) {
            // If it's already an instance or closure, return as is
            if (is_object($middleware) || is_callable($middleware)) {
                return $middleware;
            }
            
            // If it's an array with class name and parameters
            if (is_array($middleware) && isset($middleware['class'])) {
                $class = $middleware['class'];
                $parameters = $middleware['parameters'] ?? [];
                
                return new $class(...$parameters);
            }
            
            // If it's an array with 'middleware' and 'parameters' keys
            if (is_array($middleware) && isset($middleware['middleware'])) {
                $class = $middleware['middleware'];
                $parameters = $middleware['parameters'] ?? [];
                
                return new $class(...$parameters);
            }

            // If it's a string with parameters
            if (is_string($middleware) && strpos($middleware, ':') !== false) {
                [$class, $paramString] = explode(':', $middleware, 2);
                $parameters = [];
                foreach (explode(',', $paramString) as $param) {
                    if (strpos($param, '=') !== false) {
                        [$key, $value] = explode('=', $param);
                        $parameters[$key] = $value;
                    }
                }

                return new $class(...array_values($parameters));
            }
            
            // Default: instantiate the middleware class
            if (is_string($middleware) && class_exists($middleware)) {
                return new $middleware();
            }
            
            return $middleware;
        })->toArray();
    }
}

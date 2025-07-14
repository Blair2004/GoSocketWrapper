<?php

namespace GoSocket\Wrapper\Services;

use GoSocket\Wrapper\Contracts\SocketHandler;
use Illuminate\Support\Facades\File;

class HandlerDiscovery
{
    /**
     * Find a socket handler by name
     *
     * @param string $handlerName
     * @return string|null
     */
    public function findHandler(string $handlerName): ?string
    {
        $handlers = $this->discoverHandlers();

        foreach ($handlers as $handlerClass) {
            if (!class_exists($handlerClass)) {
                continue;
            }

            // Check if class name matches
            if ($handlerName === $handlerClass) {
                return $handlerClass;
            }

            // Check if the handler has a custom name
            try {
                $instance = new $handlerClass();
                
                if ($instance instanceof SocketHandler && method_exists($instance, 'getName')) {
                    $customName = $instance->getName();
                    if ($customName && $handlerName === $customName) {
                        return $handlerClass;
                    }
                }

                // Check if class basename matches
                if ($handlerName === class_basename($handlerClass)) {
                    return $handlerClass;
                }
            } catch (\Exception $e) {
                // Skip if we can't instantiate the class
                continue;
            }
        }

        return null;
    }

    /**
     * Discover all socket handlers
     *
     * @return array
     */
    public function discoverHandlers(): array
    {
        $handlers = [];
        $paths = config('gosocket.handlers_paths', []);

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $handlers = array_merge($handlers, $this->scanDirectory($path));
        }

        // Include custom registered handlers
        $customHandlers = app('gosocket.handlers')->toArray();
        $handlers = array_merge($handlers, $customHandlers);

        return array_unique($handlers);
    }

    /**
     * Scan directory for socket handler classes
     *
     * @param string $directory
     * @return array
     */
    protected function scanDirectory(string $directory): array
    {
        $handlers = [];
        
        if (!File::exists($directory)) {
            return $handlers;
        }

        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassFromFile($file->getPathname());
            
            if ($className && $this->isValidHandler($className)) {
                $handlers[] = $className;
            }
        }

        return $handlers;
    }

    /**
     * Get class name from file
     *
     * @param string $filePath
     * @return string|null
     */
    protected function getClassFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        
        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = trim($matches[1]);
        } else {
            $namespace = '';
        }

        // Extract class name
        if (preg_match('/class\s+([^\s]+)/', $content, $matches)) {
            $className = trim($matches[1]);
            
            return $namespace ? $namespace . '\\' . $className : $className;
        }

        return null;
    }

    /**
     * Check if a class is a valid socket handler
     *
     * @param string $className
     * @return bool
     */
    protected function isValidHandler(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        $reflection = new \ReflectionClass($className);
        
        // Skip abstract classes
        if ($reflection->isAbstract()) {
            return false;
        }
        
        // Check if it implements SocketHandler interface
        if ($reflection->implementsInterface(SocketHandler::class)) {
            // Check if it should be auto-loaded
            try {
                $instance = $reflection->newInstance();
                return $instance->autoLoad();
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }
}

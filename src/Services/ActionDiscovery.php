<?php

namespace GoSocket\Wrapper\Services;

use GoSocket\Wrapper\Contracts\SocketAction;
use Illuminate\Support\Facades\File;

class ActionDiscovery
{
    /**
     * Find a socket action by name
     *
     * @param string $actionName
     * @return string|null
     */
    public function findAction(string $actionName): ?string
    {
        $actions = $this->discoverActions();

        foreach ($actions as $actionClass) {
            if (!class_exists($actionClass)) {
                continue;
            }

            // Check if class name matches
            if ($actionName === $actionClass) {
                return $actionClass;
            }

            // Check if the action has a custom name
            try {
                $instance = new $actionClass();
                
                if ($instance instanceof SocketAction && method_exists($instance, 'getName')) {
                    $customName = $instance->getName();
                    if ($customName && $actionName === $customName) {
                        return $actionClass;
                    }
                }

                // Check if class basename matches
                if ($actionName === class_basename($actionClass)) {
                    return $actionClass;
                }
            } catch (\Exception $e) {
                // Skip if we can't instantiate the class
                continue;
            }
        }

        return null;
    }

    /**
     * Discover all socket actions
     *
     * @return array
     */
    public function discoverActions(): array
    {
        $actions = [];
        $paths = config('gosocket.actions_paths', []);

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $actions = array_merge($actions, $this->scanDirectory($path));
        }

        // Include custom registered handlers
        $customHandlers = app('gosocket.handlers')->toArray();
        $actions = array_merge($actions, $customHandlers);

        return array_unique($actions);
    }

    /**
     * Scan directory for socket action classes
     *
     * @param string $directory
     * @return array
     */
    protected function scanDirectory(string $directory): array
    {
        $actions = [];
        
        if (!File::exists($directory)) {
            return $actions;
        }

        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassFromFile($file->getPathname());
            
            if ($className && $this->isValidAction($className)) {
                $actions[] = $className;
            }
        }

        return $actions;
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
     * Check if a class is a valid socket action
     *
     * @param string $className
     * @return bool
     */
    protected function isValidAction(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        $reflection = new \ReflectionClass($className);
        
        // Check if it implements SocketAction interface
        if ($reflection->implementsInterface(SocketAction::class)) {
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

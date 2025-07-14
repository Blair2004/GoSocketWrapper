<?php

namespace GoSocket\Wrapper\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeHandlerCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket:make-handler {name : The name of the handler} {--path= : Custom path for the handler (relative to project root)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new socket handler';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Socket Handler';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/handler.stub';
    }

    /**
     * Override qualifyClass to handle custom paths properly
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');
        $name = str_replace('/', '\\', $name);

        // If custom path is provided, build the namespace from the path
        if ($this->option('path')) {
            $customPath = $this->option('path');
            
            // Remove .php extension if present
            if (str_ends_with($customPath, '.php')) {
                $customPath = substr($customPath, 0, -4);
            }
            
            // Remove trailing slash and convert to namespace
            $customPath = rtrim($customPath, '/');
            $namespace = str_replace('/', '\\', trim($customPath, '/'));
            $namespace = $this->ensurePsr1Compliance($namespace);
            
            // If the name is already fully qualified, return it
            if (str_contains($name, '\\')) {
                return $name;
            }
            
            // Combine namespace with class name
            return $namespace . '\\' . $name;
        }

        // Default behavior for app directory
        $rootNamespace = $this->rootNamespace();
        $defaultNamespace = $this->getDefaultNamespace(trim($rootNamespace, '\\'));
        
        if (str_starts_with($name, $rootNamespace)) {
            return $name;
        }
        
        return $defaultNamespace . '\\' . $name;
    }

    /**
     * Ensure PSR-1 compliance by capitalizing namespace segments.
     *
     * @param  string  $namespace
     * @return string
     */
    protected function ensurePsr1Compliance($namespace)
    {
        if (empty($namespace)) {
            return '';
        }
        
        // Split namespace into segments
        $segments = explode('\\', $namespace);
        
        // Capitalize the first letter of each segment (PSR-1 requirement)
        $segments = array_map(function($segment) {
            return ucfirst($segment);
        }, $segments);
        
        return implode('\\', $segments);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $customPath = $this->option('path');
        
        if ($customPath) {
            // Handle custom path
            $customPath = rtrim($customPath, '/');
            
            // Extract just the class name from the fully qualified name
            $className = basename(str_replace('\\', '/', $name));
            
            $fullPath = base_path($customPath . '/' . $className . '.php');
            
            return $fullPath;
        }
        
        // Default path: app/Socket/Handlers/
        // Remove the root namespace and default namespace to get just the class name
        $className = str_replace($this->rootNamespace(), '', $name);
        $className = str_replace($this->getDefaultNamespace(trim($this->rootNamespace(), '\\')), '', $name);
        $className = ltrim($className, '\\');
        $className = str_replace('\\', '/', $className);
        
        return $this->laravel['path'] . '/Socket/Handlers/' . $className . '.php';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Socket\\Handlers';
    }
}

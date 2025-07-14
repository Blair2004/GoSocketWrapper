<?php

namespace GoSocket\Wrapper\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeActionCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket:make-action {name : The name of the action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new socket action';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Socket Action';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/action.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Socket\Actions';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace('\\', '/', $name);
        
        return $this->laravel['path'] . '/Socket/Actions/' . str_replace($this->getNamespace($name) . '\\', '', $name) . '.php';
    }
}

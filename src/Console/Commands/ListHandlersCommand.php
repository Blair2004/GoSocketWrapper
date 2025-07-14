<?php

namespace GoSocket\Wrapper\Console\Commands;

use Illuminate\Console\Command;
use GoSocket\Wrapper\Services\ActionDiscovery;
use GoSocket\Wrapper\Contracts\SocketAction;

class ListHandlersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket:list-handlers 
                            {--detailed : Show detailed information about each handler}
                            {--only-autoload : Only show handlers that are auto-loaded}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all scanned socket action handlers';

    /**
     * The action discovery service
     *
     * @var ActionDiscovery
     */
    protected $actionDiscovery;

    /**
     * Create a new command instance.
     *
     * @param ActionDiscovery $actionDiscovery
     */
    public function __construct(ActionDiscovery $actionDiscovery)
    {
        parent::__construct();
        $this->actionDiscovery = $actionDiscovery;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning for socket action handlers...');
        $this->newLine();

        $handlers = $this->actionDiscovery->discoverActions();
        
        if (empty($handlers)) {
            $this->warn('No socket action handlers found.');
            $this->newLine();
            $this->info('Make sure you have:');
            $this->info('1. Created action classes in configured paths');
            $this->info('2. Actions implement the SocketAction interface');
            $this->info('3. Actions have autoLoad() returning true');
            return;
        }

        $this->info('Found ' . count($handlers) . ' socket action handler(s):');
        $this->newLine();

        $tableData = [];
        $detailedOutput = $this->option('detailed');
        $onlyAutoload = $this->option('only-autoload');

        foreach ($handlers as $handlerClass) {
            if (!class_exists($handlerClass)) {
                continue;
            }

            // Skip abstract classes
            $reflection = new \ReflectionClass($handlerClass);
            if ($reflection->isAbstract()) {
                continue;
            }

            try {
                $instance = new $handlerClass();
                
                if (!$instance instanceof SocketAction) {
                    continue;
                }

                $autoLoad = $instance->autoLoad();
                
                // Skip if only showing auto-loaded handlers and this one isn't
                if ($onlyAutoload && !$autoLoad) {
                    continue;
                }

                $handlerInfo = [
                    'class' => $handlerClass,
                    'name' => $instance->getName() ?: basename(str_replace('\\', '/', $handlerClass)),
                    'auto_load' => $autoLoad ? 'Yes' : 'No',
                ];

                if ($detailedOutput) {
                    $handlerInfo['middlewares'] = implode(', ', $instance->middlewares()) ?: 'None';
                    $handlerInfo['source'] = $this->getHandlerSource($handlerClass);
                }

                $tableData[] = $handlerInfo;
            } catch (\Exception $e) {
                $this->warn("Could not instantiate handler: {$handlerClass}");
                $this->error("Error: " . $e->getMessage());
            }
        }

        if (empty($tableData)) {
            $this->warn('No valid socket action handlers found.');
            return;
        }

        // Sort by handler name
        usort($tableData, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        // Prepare table headers
        $headers = ['Handler Name', 'Class', 'Auto Load'];
        if ($detailedOutput) {
            $headers[] = 'Middlewares';
            $headers[] = 'Source';
        }

        // Prepare table rows
        $rows = [];
        foreach ($tableData as $handler) {
            $row = [
                $handler['name'],
                $handler['class'],
                $handler['auto_load'],
            ];
            
            if ($detailedOutput) {
                $row[] = $handler['middlewares'];
                $row[] = $handler['source'];
            }
            
            $rows[] = $row;
        }

        $this->table($headers, $rows);
        
        $this->newLine();
        $this->info('Summary:');
        $this->info('- Total handlers: ' . count($tableData));
        $this->info('- Auto-loaded: ' . count(array_filter($tableData, fn($h) => $h['auto_load'] === 'Yes')));
        $this->info('- Manual registration: ' . count(array_filter($tableData, fn($h) => $h['auto_load'] === 'No')));
        
        $this->newLine();
        $this->info('Configuration paths:');
        $paths = config('gosocket.actions_paths', []);
        foreach ($paths as $path) {
            $exists = is_dir($path) ? '✓' : '✗';
            $this->info("  {$exists} {$path}");
        }
        
        $customHandlers = app('gosocket.handlers')->toArray();
        if (!empty($customHandlers)) {
            $this->newLine();
            $this->info('Custom registered handlers: ' . count($customHandlers));
        }
    }

    /**
     * Determine the source of a handler
     *
     * @param string $handlerClass
     * @return string
     */
    protected function getHandlerSource(string $handlerClass): string
    {
        $customHandlers = app('gosocket.handlers')->toArray();
        
        if (in_array($handlerClass, $customHandlers)) {
            return 'Custom Registration';
        }

        $reflection = new \ReflectionClass($handlerClass);
        $fileName = $reflection->getFileName();
        
        if (!$fileName) {
            return 'Unknown';
        }

        $paths = config('gosocket.actions_paths', []);
        
        foreach ($paths as $path) {
            $realPath = realpath($path);
            if ($realPath && strpos($fileName, $realPath) === 0) {
                return 'Auto-discovery: ' . $path;
            }
        }

        return 'File System';
    }
}

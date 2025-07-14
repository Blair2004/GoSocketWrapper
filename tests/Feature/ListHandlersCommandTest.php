<?php

namespace GoSocket\Wrapper\Tests\Feature;

use GoSocket\Wrapper\Tests\TestCase;
use GoSocket\Wrapper\Console\Commands\ListHandlersCommand;
use GoSocket\Wrapper\Services\ActionDiscovery;

class ListHandlersCommandTest extends TestCase
{
    public function test_command_runs_successfully()
    {
        $this->artisan('socket:list-handlers')
            ->expectsOutput('Scanning for socket action handlers...')
            ->assertExitCode(0);
    }

    public function test_command_shows_handlers_when_found()
    {
        $this->artisan('socket:list-handlers')
            ->expectsOutput('Scanning for socket action handlers...')
            ->expectsOutputToContain('Found')
            ->expectsOutputToContain('socket action handler(s):')
            ->assertExitCode(0);
    }

    public function test_detailed_option_shows_additional_info()
    {
        $this->artisan('socket:list-handlers --detailed')
            ->expectsOutput('Scanning for socket action handlers...')
            ->expectsOutputToContain('Middlewares')
            ->expectsOutputToContain('Source')
            ->assertExitCode(0);
    }

    public function test_only_autoload_option_filters_results()
    {
        $this->artisan('socket:list-handlers --only-autoload')
            ->expectsOutput('Scanning for socket action handlers...')
            ->assertExitCode(0);
    }

    public function test_combined_options_work_together()
    {
        $this->artisan('socket:list-handlers --detailed --only-autoload')
            ->expectsOutput('Scanning for socket action handlers...')
            ->assertExitCode(0);
    }

    public function test_command_shows_summary_information()
    {
        $this->artisan('socket:list-handlers')
            ->expectsOutputToContain('Summary:')
            ->expectsOutputToContain('Total handlers:')
            ->expectsOutputToContain('Auto-loaded:')
            ->expectsOutputToContain('Configuration paths:')
            ->assertExitCode(0);
    }

    public function test_command_shows_configuration_paths()
    {
        $this->artisan('socket:list-handlers')
            ->expectsOutputToContain('Configuration paths:')
            ->expectsOutputToContain('app/Socket/Actions')
            ->assertExitCode(0);
    }
}

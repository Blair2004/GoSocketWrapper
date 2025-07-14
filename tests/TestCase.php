<?php

namespace GoSocket\Wrapper\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use GoSocket\Wrapper\GoSocketServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            GoSocketServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        $app['config']->set('gosocket.socket_server_url', 'ws://localhost:8080');
        $app['config']->set('gosocket.socket_http_url', 'http://localhost:8081');
        $app['config']->set('gosocket.socket_token', 'test-token');
    }
}

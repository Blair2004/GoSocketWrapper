<?php

namespace GoSocket\Wrapper\Tests\Feature;

use GoSocket\Wrapper\Tests\TestCase;
use GoSocket\Wrapper\Services\ActionDiscovery;
use GoSocket\Wrapper\Actions\PingAction;

class ActionDiscoveryTest extends TestCase
{
    public function test_can_discover_ping_action()
    {
        $discovery = new ActionDiscovery();
        
        $actionClass = $discovery->findAction('ping');
        
        $this->assertEquals(PingAction::class, $actionClass);
    }

    public function test_can_discover_action_by_class_name()
    {
        $discovery = new ActionDiscovery();
        
        $actionClass = $discovery->findAction(PingAction::class);
        
        $this->assertEquals(PingAction::class, $actionClass);
    }
}

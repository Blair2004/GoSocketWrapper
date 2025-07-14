<?php

namespace GoSocket\Wrapper\Tests\Feature;

use GoSocket\Wrapper\Tests\TestCase;
use GoSocket\Wrapper\Services\ActionDiscovery;
use GoSocket\Wrapper\Actions\BaseAction;
use GoSocket\Wrapper\Actions\PingAction;

class AbstractClassFilteringTest extends TestCase
{
    public function test_abstract_classes_are_excluded_from_discovery()
    {
        $discovery = new ActionDiscovery();
        
        $actions = $discovery->discoverActions();
        
        // BaseAction should not be in the discovered actions
        $this->assertNotContains(BaseAction::class, $actions);
        
        // But concrete actions should be present
        $this->assertContains(PingAction::class, $actions);
    }

    public function test_cannot_find_abstract_action_by_name()
    {
        $discovery = new ActionDiscovery();
        
        $actionClass = $discovery->findAction('BaseAction');
        
        $this->assertNull($actionClass);
    }

    public function test_isValidAction_returns_false_for_abstract_classes()
    {
        $discovery = new ActionDiscovery();
        
        $reflection = new \ReflectionMethod($discovery, 'isValidAction');
        $reflection->setAccessible(true);
        
        $result = $reflection->invoke($discovery, BaseAction::class);
        
        $this->assertFalse($result);
    }

    public function test_isValidAction_returns_true_for_concrete_classes()
    {
        $discovery = new ActionDiscovery();
        
        $reflection = new \ReflectionMethod($discovery, 'isValidAction');
        $reflection->setAccessible(true);
        
        $result = $reflection->invoke($discovery, PingAction::class);
        
        $this->assertTrue($result);
    }
}

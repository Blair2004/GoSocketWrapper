<?php

namespace GoSocket\Wrapper\Actions;

use GoSocket\Wrapper\Contracts\SocketAction;

abstract class BaseAction implements SocketAction
{
    /**
     * Get middleware for this action
     *
     * @return array
     */
    public function middlewares(): array
    {
        return [];
    }

    /**
     * Get the name of the action
     * If not implemented, the class name will be used
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return null;
    }

    /**
     * Determine if this action should be auto-loaded
     *
     * @return bool
     */
    public function autoLoad(): bool
    {
        return true;
    }
}

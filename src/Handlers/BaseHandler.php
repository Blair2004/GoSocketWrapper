<?php

namespace GoSocket\Wrapper\Handlers;

use GoSocket\Wrapper\Contracts\SocketHandler;

abstract class BaseHandler implements SocketHandler
{
    /**
     * Get middleware for this handler
     *
     * @return array
     */
    public function middlewares(): array
    {
        return [];
    }

    /**
     * Get the name of the handler
     * If not implemented, the class name will be used
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return null;
    }

    /**
     * Determine if this handler should be auto-loaded
     *
     * @return bool
     */
    public function autoLoad(): bool
    {
        return true;
    }
}

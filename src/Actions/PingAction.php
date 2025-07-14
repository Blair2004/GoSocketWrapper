<?php

namespace GoSocket\Wrapper\Actions;

class PingAction extends BaseAction
{
    /**
     * Handle the ping action
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void
    {
        // Handle ping - typically just log or respond with pong
        // This is automatically handled by the socket server
    }

    /**
     * Get the name of the action
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return 'ping';
    }
}

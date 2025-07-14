<?php

namespace GoSocket\Wrapper\Handlers;

class PingHandler extends BaseHandler
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
     * Get the name of the handler
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return 'ping';
    }
}

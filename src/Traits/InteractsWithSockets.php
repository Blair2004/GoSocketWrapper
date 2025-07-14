<?php

namespace GoSocket\Wrapper\Traits;

trait InteractsWithSockets
{
    /**
     * Determine if the event should broadcast to everyone
     *
     * @return bool
     */
    public function broadcastToEveryone(): bool
    {
        return false;
    }

    /**
     * Determine if the current user should be excluded from broadcast
     *
     * @return bool
     */
    public function dontBroadcastToCurrentUser(): bool
    {
        return false;
    }

    /**
     * Get the socket channel for broadcasting
     *
     * @return string|array
     */
    public function broadcastOn()
    {
        return 'global';
    }

    /**
     * Get the broadcast event name
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return \GoSocket\Wrapper\Helpers\LaravelHelper::classBasename($this);
    }

    /**
     * Get the data to broadcast
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [];
    }
}

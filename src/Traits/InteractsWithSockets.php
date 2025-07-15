<?php

namespace GoSocket\Wrapper\Traits;

trait InteractsWithSockets
{
    /**
     * Client ID for client-specific broadcasting
     */
    protected $clientId = null;

    /**
     * User ID for user-specific broadcasting
     */
    protected $targetUserId = null;

    /**
     * Broadcast type override
     */
    protected $broadcastType = null;

    /**
     * Set the client ID for client-specific broadcasting
     *
     * @param string $clientId
     * @return $this
     */
    public function toClient(string $clientId): self
    {
        $this->clientId = $clientId;
        $this->broadcastType = 'client';
        return $this;
    }

    /**
     * Set the user ID for user-specific broadcasting
     *
     * @param string $userId
     * @return $this
     */
    public function toUser(string $userId): self
    {
        $this->targetUserId = $userId;
        $this->broadcastType = 'user';
        return $this;
    }

    /**
     * Set broadcasting to all authenticated users
     *
     * @return $this
     */
    public function toAuthenticated(): self
    {
        $this->broadcastType = 'authenticated';
        return $this;
    }

    /**
     * Set broadcasting to all users except specific user
     *
     * @param string $excludeUserId
     * @return $this
     */
    public function toUsersExcept(string $excludeUserId): self
    {
        $this->targetUserId = $excludeUserId;
        $this->broadcastType = 'user_except';
        return $this;
    }

    /**
     * Set broadcasting to global (all clients)
     *
     * @return $this
     */
    public function toGlobal(): self
    {
        $this->broadcastType = 'global';
        return $this;
    }

    /**
     * Get the client ID for client-specific broadcasting
     *
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * Get the target user ID
     *
     * @return string|null
     */
    public function getTargetUserId(): ?string
    {
        return $this->targetUserId;
    }

    /**
     * Get the broadcast type
     *
     * @return string|null
     */
    public function getBroadcastType(): ?string
    {
        return $this->broadcastType;
    }

    /**
     * Determine if the event should broadcast to everyone
     *
     * @return bool
     */
    public function broadcastToEveryone(): bool
    {
        return $this->broadcastType === 'global';
    }

    /**
     * Determine if the current user should be excluded from broadcast
     *
     * @return bool
     */
    public function dontBroadcastToCurrentUser(): bool
    {
        return $this->broadcastType === 'user_except';
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

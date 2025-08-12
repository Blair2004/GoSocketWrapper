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

    /**
     * Dispatch this event to a specific client
     *
     * @param string $clientId
     * @param mixed ...$args Constructor arguments for the event
     * @return void
     */
    public static function dispatchToClient(string $clientId, ...$args): void
    {
        $event = new static(...$args);
        $event->toClient($clientId);
        
        // Dispatch as a regular Laravel event so it appears in Laravel's event system
        // The EventListener will pick this up and handle the socket broadcasting
        \Illuminate\Support\Facades\Event::dispatch($event);
    }

    /**
     * Dispatch this event to a specific user (all their connections)
     *
     * @param string $userId
     * @param mixed ...$args Constructor arguments for the event
     * @return void
     */
    public static function dispatchToUser(string $userId, ...$args): void
    {
        $event = new static(...$args);
        $event->toUser($userId);
        
        // Dispatch as a regular Laravel event so it appears in Laravel's event system
        \Illuminate\Support\Facades\Event::dispatch($event);
    }

    /**
     * Dispatch this event to all authenticated users
     *
     * @param mixed ...$args Constructor arguments for the event
     * @return void
     */
    public static function dispatchToAuthenticated(...$args): void
    {
        $event = new static(...$args);
        $event->toAuthenticated();
        
        // Dispatch as a regular Laravel event so it appears in Laravel's event system
        \Illuminate\Support\Facades\Event::dispatch($event);
    }

    /**
     * Dispatch this event to all users except one
     *
     * @param string $excludeUserId
     * @param mixed ...$args Constructor arguments for the event
     * @return void
     */
    public static function dispatchToUsersExcept(string $excludeUserId, ...$args): void
    {
        $event = new static(...$args);
        $event->toUsersExcept($excludeUserId);
        
        // Dispatch as a regular Laravel event so it appears in Laravel's event system
        \Illuminate\Support\Facades\Event::dispatch($event);
    }

    /**
     * Dispatch this event globally to all clients
     *
     * @param mixed ...$args Constructor arguments for the event
     * @return void
     */
    public static function dispatchToGlobal(...$args): void
    {
        $event = new static(...$args);
        $event->toGlobal();
        
        // Dispatch as a regular Laravel event so it appears in Laravel's event system
        \Illuminate\Support\Facades\Event::dispatch($event);
    }

    /**
     * Dispatch this event to a specific channel
     *
     * @param string $channel
     * @param mixed ...$args Constructor arguments for the event
     * @return void
     */
    public static function dispatchToChannel(string $channel, ...$args): void
    {
        $event = new static(...$args);
        // Channel-based is the default behavior, so we don't need to set broadcast type
        
        // Dispatch as a regular Laravel event so it appears in Laravel's event system
        \Illuminate\Support\Facades\Event::dispatch($event);
    }
}

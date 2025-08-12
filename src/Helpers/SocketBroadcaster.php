<?php

namespace GoSocket\Wrapper\Helpers;

use GoSocket\Wrapper\Traits\InteractsWithSockets;
use Illuminate\Support\Facades\Event;

class SocketBroadcaster
{
    /**
     * Dispatch an event to a specific client
     *
     * @param string $clientId
     * @param object $event
     * @return void
     */
    public static function toClient(string $clientId, object $event): void
    {
        if (in_array(InteractsWithSockets::class, class_uses_recursive($event))) {
            $event->toClient($clientId);
            static::dispatch($event);
        }
    }

    /**
     * Dispatch an event to a specific user (all their connections)
     *
     * @param string $userId
     * @param object $event
     * @return void
     */
    public static function toUser(string $userId, object $event): void
    {
        if (in_array(InteractsWithSockets::class, class_uses_recursive($event))) {
            $event->toUser($userId);
            static::dispatch($event);
        }
    }

    /**
     * Dispatch an event to all authenticated users
     *
     * @param object $event
     * @return void
     */
    public static function toAuthenticated(object $event): void
    {
        if (in_array(InteractsWithSockets::class, class_uses_recursive($event))) {
            $event->toAuthenticated();
            static::dispatch($event);
        }
    }

    /**
     * Dispatch an event to all users except one
     *
     * @param string $excludeUserId
     * @param object $event
     * @return void
     */
    public static function toUsersExcept(string $excludeUserId, object $event): void
    {
        if (in_array(InteractsWithSockets::class, class_uses_recursive($event))) {
            $event->toUsersExcept($excludeUserId);
            static::dispatch($event);
        }
    }

    /**
     * Dispatch an event globally to all clients
     *
     * @param object $event
     * @return void
     */
    public static function toGlobal(object $event): void
    {
        if (in_array(InteractsWithSockets::class, class_uses_recursive($event))) {
            $event->toGlobal();
            static::dispatch($event);
        }
    }

    /**
     * Dispatch the event to the socket broadcast system
     *
     * @param object $event
     * @return void
     */
    protected static function dispatch(object $event): void
    {
        Event::dispatch('socket.broadcast', [$event]);
    }
}

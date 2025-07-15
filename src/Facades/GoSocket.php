<?php

namespace GoSocket\Wrapper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool broadcast(array $data)
 * @method static bool sendToChannel(string $channel, string $event, array $data = [], array $options = [])
 * @method static bool sendToUser(int $userId, string $event, array $data = [])
 * @method static bool sendGlobal(string $event, array $data = [])
 * @method static void toClient(string $clientId, object $event)
 * @method static void toUser(string $userId, object $event)
 * @method static void toAuthenticated(object $event)
 * @method static void toUsersExcept(string $excludeUserId, object $event)
 * @method static void toGlobal(object $event)
 */
class GoSocket extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gosocket.client';
    }
}

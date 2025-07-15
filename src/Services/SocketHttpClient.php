<?php

namespace GoSocket\Wrapper\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;

class SocketHttpClient
{
    /**
     * Socket HTTP server URL
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Authentication token
     *
     * @var string
     */
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('gosocket.socket_http_url');
        $this->token = config('gosocket.socket_token');
    }

    /**
     * Send a broadcast request to the socket server
     *
     * @param array $data
     * @return bool
     */
    public function broadcast(array $data): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/broadcast', $data);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send a message to a specific channel
     *
     * @param string $channel
     * @param string $event
     * @param array $data
     * @param array $options
     * @return bool
     */
    public function sendToChannel(string $channel, string $event, array $data = [], array $options = []): bool
    {
        return $this->broadcast([
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
            'options' => $options,
        ]);
    }

    /**
     * Send a message to a specific user
     *
     * @param int $userId
     * @param string $event
     * @param array $data
     * @return bool
     */
    public function sendToUser(int $userId, string $event, array $data = []): bool
    {
        return $this->broadcast([
            'user_id' => $userId,
            'event' => $event,
            'data' => $data,
        ]);
    }

    /**
     * Send a global broadcast
     *
     * @param string $event
     * @param array $data
     * @return bool
     */
    public function sendGlobal(string $event, array $data = []): bool
    {
        return $this->broadcast([
            'channel' => 'global',
            'event' => $event,
            'data' => $data,
            'broadcast_to_everyone' => true,
        ]);
    }

    /**
     * Send an event to a specific client
     *
     * @param string $clientId
     * @param object $event
     * @return bool
     */
    public function toClient(string $clientId, object $event): bool
    {
        if (!$this->hasInteractsWithSockets($event)) {
            return false;
        }

        $event->toClient($clientId);
        return $this->dispatchSocketEvent($event);
    }

    /**
     * Send an event to a specific user (all their connections)
     *
     * @param string $userId
     * @param object $event
     * @return bool
     */
    public function toUser(string $userId, object $event): bool
    {
        if (!$this->hasInteractsWithSockets($event)) {
            return false;
        }

        $event->toUser($userId);
        return $this->dispatchSocketEvent($event);
    }

    /**
     * Send an event to all authenticated users
     *
     * @param object $event
     * @return bool
     */
    public function toAuthenticated(object $event): bool
    {
        if (!$this->hasInteractsWithSockets($event)) {
            return false;
        }

        $event->toAuthenticated();
        return $this->dispatchSocketEvent($event);
    }

    /**
     * Send an event to all users except one
     *
     * @param string $excludeUserId
     * @param object $event
     * @return bool
     */
    public function toUsersExcept(string $excludeUserId, object $event): bool
    {
        if (!$this->hasInteractsWithSockets($event)) {
            return false;
        }

        $event->toUsersExcept($excludeUserId);
        return $this->dispatchSocketEvent($event);
    }

    /**
     * Send an event globally to all clients
     *
     * @param object $event
     * @return bool
     */
    public function toGlobal(object $event): bool
    {
        if (!$this->hasInteractsWithSockets($event)) {
            return false;
        }

        $event->toGlobal();
        return $this->dispatchSocketEvent($event);
    }

    /**
     * Check if event uses InteractsWithSockets trait
     *
     * @param object $event
     * @return bool
     */
    protected function hasInteractsWithSockets(object $event): bool
    {
        $traits = class_uses_recursive(get_class($event));
        return in_array(\GoSocket\Wrapper\Traits\InteractsWithSockets::class, $traits);
    }

    /**
     * Dispatch the socket event
     *
     * @param object $event
     * @return bool
     */
    protected function dispatchSocketEvent(object $event): bool
    {
        try {
            Event::dispatch('socket.broadcast', [$event]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

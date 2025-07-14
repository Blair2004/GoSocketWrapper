<?php

namespace GoSocket\Wrapper\Services;

use Illuminate\Support\Facades\Http;

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
}

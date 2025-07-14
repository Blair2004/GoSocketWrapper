<?php

namespace GoSocket\Wrapper\Listeners;

use Illuminate\Support\Facades\Http;
use GoSocket\Wrapper\Traits\InteractsWithSockets;

class EventListener
{
    /**
     * Handle the event
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function handle($eventName, $data)
    {
        // Only process events that have the InteractsWithSockets trait
        if (!$this->shouldBroadcast($data)) {
            return;
        }

        $event = $data[0] ?? null;
        
        if (!$event) {
            return;
        }

        $this->broadcastToSocket($event);
    }

    /**
     * Check if the event should be broadcast to socket
     *
     * @param array $data
     * @return bool
     */
    protected function shouldBroadcast($data): bool
    {
        $event = $data[0] ?? null;
        
        if (!is_object($event)) {
            return false;
        }

        // Check if event uses InteractsWithSockets trait
        $traits = class_uses_recursive(get_class($event));
        
        if (!in_array(InteractsWithSockets::class, $traits)) {
            return false;
        }

        // Check if event has required methods
        return method_exists($event, 'broadcastToEveryone') || 
               method_exists($event, 'dontBroadcastToCurrentUser');
    }

    /**
     * Broadcast event to socket server
     *
     * @param object $event
     * @return void
     */
    protected function broadcastToSocket($event): void
    {
        try {
            $socketUrl = config('gosocket.socket_http_url');
            $token = config('gosocket.socket_token');
            $endpoint = config('gosocket.broadcasting.endpoint', '/api/broadcast');

            if (!$socketUrl || !$token) {
                return;
            }

            $payload = [
                'event' => method_exists($event, 'broadcastAs') ? $event->broadcastAs() : class_basename(get_class($event)),
                'channel' => method_exists($event, 'broadcastOn') ? $event->broadcastOn() : 'global',
                'data' => method_exists($event, 'broadcastWith') ? $event->broadcastWith() : [],
                'broadcast_to_everyone' => method_exists($event, 'broadcastToEveryone') ? $event->broadcastToEveryone() : false,
                'exclude_current_user' => method_exists($event, 'dontBroadcastToCurrentUser') ? $event->dontBroadcastToCurrentUser() : false,
            ];

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($socketUrl . $endpoint, $payload);

        } catch (\Exception $e) {
            // Log error but don't break the application
            logger()->error('Failed to broadcast event to socket server', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }
}

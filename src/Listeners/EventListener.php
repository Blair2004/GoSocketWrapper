<?php

namespace GoSocket\Wrapper\Listeners;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
                'broadcast_type' => $this->determineBroadcastType($event),
                'client_id' => method_exists($event, 'getClientId') ? $event->getClientId() : null,
                'user_id' => method_exists($event, 'getTargetUserId') ? $event->getTargetUserId() : null,
            ];

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($socketUrl . $endpoint, $payload);

        } catch (\Exception $e) {
            // Log error but don't break the application
            Log::error('Failed to broadcast event to socket server', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine the broadcast type based on event configuration
     *
     * @param object $event
     * @return string
     */
    protected function determineBroadcastType($event): string
    {
        // Check if event has explicit broadcast type
        if (method_exists($event, 'getBroadcastType') && $event->getBroadcastType()) {
            return $event->getBroadcastType();
        }

        // Check if event has client ID (highest priority)
        if (method_exists($event, 'getClientId') && $event->getClientId()) {
            return 'client';
        }

        // Check if event has target user ID
        if (method_exists($event, 'getTargetUserId') && $event->getTargetUserId()) {
            // Check if it's exclude type
            if (method_exists($event, 'dontBroadcastToCurrentUser') && $event->dontBroadcastToCurrentUser()) {
                return 'user_except';
            }
            return 'user';
        }

        // Check if it's global broadcast
        if (method_exists($event, 'broadcastToEveryone') && $event->broadcastToEveryone()) {
            return 'global';
        }

        // Check if it's authenticated broadcast
        if (method_exists($event, 'broadcastOn')) {
            $channel = $event->broadcastOn();
            if ($channel === 'authenticated') {
                return 'authenticated';
            }
        }

        // Default to channel-based broadcasting
        return 'channel';
    }
}

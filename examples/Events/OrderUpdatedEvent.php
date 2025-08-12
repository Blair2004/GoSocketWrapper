<?php

namespace GoSocket\Wrapper\Examples\Events;

use GoSocket\Wrapper\Traits\InteractsWithSockets;

class OrderUpdatedEvent
{
    use InteractsWithSockets;

    public $order;
    public $message;

    public function __construct($order, $message = 'Order updated')
    {
        $this->order = $order;
        $this->message = $message;
    }

    public function broadcastAs(): string
    {
        return 'order_updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => $this->order,
            'message' => $this->message,
            'timestamp' => date('c')
        ];
    }
}

// Usage examples:
// OrderUpdatedEvent::dispatchToClient('client-123', $order, 'Your order has been updated');
// OrderUpdatedEvent::dispatchToUser('user-456', $order);
// OrderUpdatedEvent::dispatchToAuthenticated($order, 'Order #123 has been updated');
// OrderUpdatedEvent::dispatchToGlobal($order, 'New order received');

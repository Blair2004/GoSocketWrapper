<?php
namespace GoSocket\Wrapper\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeforeLeavePrivateChannelEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $channel,
        public array $data,
        public array $auth
    ) {
        // Initialization code can go here if needed
    }
}
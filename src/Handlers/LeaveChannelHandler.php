<?php

namespace GoSocket\Wrapper\Handlers;

use GoSocket\Wrapper\Events\BeforeJoinPrivateChannelEvent;
use GoSocket\Wrapper\Events\BeforeLeavePrivateChannelEvent;

class LeaveChannelHandler extends BaseHandler
{
    /**
     * Handle the join channel action
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void
    {
        $channel = $payload['channel'] ?? null;
        $isPrivate = $payload['private'] ?? false;

        BeforeLeavePrivateChannelEvent::dispatch( $channel, $payload[ 'data' ], $payload[ 'auth' ] );
    }

    /**
     * Get the name of the handler
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return 'leave_channel';
    }
}

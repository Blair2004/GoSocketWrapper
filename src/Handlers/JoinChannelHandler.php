<?php

namespace GoSocket\Wrapper\Handlers;

use GoSocket\Wrapper\Events\BeforeJoinPrivateChannelEvent;

class JoinChannelHandler extends BaseHandler
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

        if (!$channel) {
            throw new \Exception('Channel name is required');
        }

        // Additional logic for channel access control can be added here
        // For example, checking if the user has permission to join the channel
        
        if ($isPrivate) {
            $this->validatePrivateChannelAccess( $channel, $payload[ 'data' ], $payload[ 'auth' ] );
        }
    }

    /**
     * Validate private channel access
     *
     * @param string $channel
     * @param int $userId
     * @return void
     */
    protected function validatePrivateChannelAccess(string $channel, array $data, array $auth ): void
    {
        // Implement your private channel access logic here
        // For example:
        // - Check if user has permission to join this channel
        // - Validate channel existence
        // - Check user roles/permissions
        BeforeJoinPrivateChannelEvent::dispatch( $channel, $data, $auth );
    }

    /**
     * Get the name of the handler
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return 'join_channel';
    }
}

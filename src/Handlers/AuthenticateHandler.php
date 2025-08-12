<?php

namespace GoSocket\Wrapper\Handlers;

use App\Models\User;
use GoSocket\Wrapper\Events\AuthenticationFailedEvent;
use GoSocket\Wrapper\Events\AuthenticationSucceedEvent;
use GoSocket\Wrapper\Facades\GoSocket;

class AuthenticateHandler extends BaseHandler
{
    /**
     * Handle the authenticate action
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void
    {
        // Authentication is typically handled by the socket server
        // This handler can be used for additional authentication logic
        
        if ( ! isset( $payload[ 'auth' ] ) || empty( $payload[ 'auth' ][ 'user_id' ] ) ) {
            AuthenticationFailedEvent::dispatch(
                $payload[ 'auth' ][ 'id' ], [
                    'error' => 'Authentication failed: User ID is missing or empty.'
                ]
            );

            return;
        }

        $user = User::find( $payload[ 'auth' ][ 'user_id' ] );

        if ( ! $user ) {
            AuthenticationFailedEvent::dispatchToClient(
                $payload[ 'auth' ][ 'id' ], [
                    'error' => 'Authentication failed: User not found.'
                ]
            );
            
            return;
        }

        AuthenticationSucceedEvent::dispatchToClient(
            $payload[ 'auth' ][ 'id' ],
            $user->toArray()
        );
    }

    /**
     * Get the name of the handler
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return 'client_authentication';
    }

    /**
     * Get middleware for this handler
     *
     * @return array
     */
    public function middlewares(): array
    {
        return [
            // Don't require authentication for the authenticate handler itself
        ];
    }
}

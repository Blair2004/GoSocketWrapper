<?php

namespace GoSocket\Wrapper\Handlers;

use App\Models\User;
use GoSocket\Wrapper\Events\AuthenticationFailedEvent;
use GoSocket\Wrapper\Events\AuthenticationSucceedEvent;

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
                __( 'Authentication failed: No authentication data provided or missing "user_id".' ),
                $payload
            );

            return;
        }

        $user = User::find( $payload[ 'auth' ][ 'user_id' ] );

        if ( ! $user ) {
            AuthenticationFailedEvent::dispatch(
                __( 'Authentication failed: User not found.' ),
                $payload
            );
            
            return;
        }

        AuthenticationSucceedEvent::dispatch(
            __( 'Authentication succeeded.' ), [
                'payload' => $payload,
                'user'    => $user,
            ]
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

<?php

namespace GoSocket\Wrapper\Listeners;

use Exception;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token\Builder;

class UserLoginListener
{
    /**
     * Handle the event
     *
     * @param Login $event
     * @return void
     */
    public function handle(Login $event): void
    {
        $this->generateSocketJWT($event->user);
    }

    /**
     * Generate JWT token for socket authentication
     *
     * @param mixed $user
     * @return void
     */
    protected function generateSocketJWT($user): void
    {
        try {
            $signingKey = config('gosocket.jwt_secret');
            
            if (!$signingKey) {
                return;
            }

            $builder = Builder::new(new JoseEncoder(), ChainedFormatter::default());
            $algorithm = new Sha256();
            $key = InMemory::plainText($signingKey);

            $token = $builder
                ->issuedBy(config('gosocket.jwt_app_id', 'go-socket'))
                ->permittedFor(config('gosocket.jwt_app_url', 'http://localhost'))
                ->expiresAt(Carbon::now()->addWeek()->toDateTimeImmutable())
                ->withClaim('user_id', $user->id)
                ->withClaim('username', $user->username ?? $user->name ?? '')
                ->withClaim('email', $user->email)
                ->getToken($algorithm, $key);

            $user->socket_jwt = $token->toString();
            $user->save();
            
        } catch (\Exception $e) {
            throw new Exception( 'Error generating socket JWT: ' . $e->getMessage() );
        }
    }
}

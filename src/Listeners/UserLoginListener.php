<?php

namespace GoSocket\Wrapper\Listeners;

use Illuminate\Auth\Events\Login;
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
            $signingKey = config('gosocket.socket_signing_key');
            
            if (!$signingKey) {
                return;
            }

            $builder = Builder::new(new JoseEncoder(), ChainedFormatter::default());
            $algorithm = new Sha256();
            $key = InMemory::plainText($signingKey);

            $token = $builder
                ->issuedBy(config('app.url', 'localhost'))
                ->permittedFor(config('app.url', 'localhost'))
                ->expiresAt(now()->addWeek()->toDateTimeImmutable())
                ->withClaim('user_id', $user->id)
                ->withClaim('username', $user->username ?? $user->name ?? '')
                ->withClaim('email', $user->email)
                ->getToken($algorithm, $key);

            $user->socket_jwt = $token->toString();
            $user->save();
            
        } catch (\Exception $e) {
            // Log error but don't break login process
            logger()->error('Failed to generate socket JWT', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

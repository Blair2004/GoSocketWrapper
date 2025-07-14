<?php

namespace GoSocket\Wrapper\Contracts;

interface SocketHandler
{
    /**
     * Handle the socket action
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void;

    /**
     * Get middleware for this handler
     *
     * @return array
     */
    public function middlewares(): array;

    /**
     * Get the name of the handler
     * If not implemented, the class name will be used
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Determine if this handler should be auto-loaded
     *
     * @return bool
     */
    public function autoLoad(): bool;
}

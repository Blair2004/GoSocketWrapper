<?php

namespace GoSocket\Wrapper\Contracts;

interface SocketAction
{
    /**
     * Handle the socket action
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void;

    /**
     * Get middleware for this action
     *
     * @return array
     */
    public function middlewares(): array;

    /**
     * Get the name of the action
     * If not implemented, the class name will be used
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Determine if this action should be auto-loaded
     *
     * @return bool
     */
    public function autoLoad(): bool;
}

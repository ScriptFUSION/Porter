<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector\Recoverable;

/**
 * Provides methods for handling recoverable exceptions.
 *
 * This interface supports a prototype cloning model that guarantees the object can be cloned and reset to its
 * initial state at any time, any number of times. This is needed because a given import can spawn any number of
 * subsequent fetches, some of which may execute concurrently, and all of which share the same exception handler
 * prototype.
 *
 * This approach is better than relying on __clone because handlers may employ generators which cannot be cloned.
 * If generators are part of the object's state they must be recreated during initialize().
 */
interface RecoverableExceptionHandler
{
    /**
     * Initializes this handler to its starting state. Should be idempotent because it may be called multiple times.
     *
     * This method must always be called before the first call to __invoke.
     *
     * @return void
     */
    public function initialize(): void;

    /**
     * Handles a recoverable exception.
     *
     * @return mixed
     */
    public function __invoke(RecoverableException $exception);
}

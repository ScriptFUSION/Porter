<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;

/**
 * The exception that is throw when a resource is incompatible with an import operation.
 */
final class IncompatibleResourceException extends \LogicException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function createMustImplementInterface(): self
    {
        return new self('Cannot import one: resource does not implement ' . SingleRecordResource::class . '.');
    }

    public static function createMustNotImplementInterface(): self
    {
        return new self('This is a single record resource. Try calling importOne() instead.');
    }

    public static function createMustNotImplementInterfaceAsync(): self
    {
        return new self('This is a single record resource. Try calling importOneAsync() instead.');
    }
}

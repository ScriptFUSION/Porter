<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;

/**
 * The exception that is throw when a resource is incompatible with an importOne operation because it is not marked
 * with the single record interface.
 */
final class IncompatibleResourceException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Cannot import one: resource does not implement ' . SingleRecordResource::class . '.');
    }
}

<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Connector\Recoverable\ExponentialAsyncDelayRecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Transform\AnysyncTransformer;
use ScriptFUSION\Porter\Transform\AsyncTransformer;

/**
 * Specifies which resource to import asynchronously and how the data should be transformed.
 */
class AsyncImportSpecification extends Specification
{
    private $asyncResource;

    public function __construct(AsyncResource $resource)
    {
        $this->asyncResource = $resource;

        parent::__construct();
    }

    public function __clone()
    {
        $this->asyncResource = clone $this->asyncResource;

        parent::__clone();
    }

    final public function getAsyncResource(): AsyncResource
    {
        return $this->asyncResource;
    }

    final public function addTransformer(AnysyncTransformer $transformer): Specification
    {
        if (!$transformer instanceof AsyncTransformer) {
            throw new IncompatibleTransformerException(
                'Transformer does not implement interface: ' . AsyncTransformer::class . '.'
            );
        }

        return parent::addTransformer($transformer);
    }

    protected static function createDefaultRecoverableExceptionHandler(): RecoverableExceptionHandler
    {
        return new ExponentialAsyncDelayRecoverableExceptionHandler;
    }
}

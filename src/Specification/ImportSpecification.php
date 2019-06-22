<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Connector\Recoverable\ExponentialSleepRecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Transform\AnysyncTransformer;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * Specifies which resource to import and how the data should be transformed.
 */
class ImportSpecification extends Specification
{
    /**
     * @var ProviderResource
     */
    private $resource;

    public function __construct(ProviderResource $resource)
    {
        $this->resource = $resource;

        parent::__construct();
    }

    public function __clone()
    {
        $this->resource = clone $this->resource;

        parent::__clone();
    }

    /**
     * @return ProviderResource
     */
    final public function getResource(): ProviderResource
    {
        return $this->resource;
    }

    final public function addTransformer(AnysyncTransformer $transformer): Specification
    {
        if (!$transformer instanceof Transformer) {
            throw new IncompatibleTransformerException(
                'Transformer does not implement interface: ' . Transformer::class . '.'
            );
        }

        return parent::addTransformer($transformer);
    }

    protected static function createDefaultRecoverableExceptionHandler(): RecoverableExceptionHandler
    {
        return new ExponentialSleepRecoverableExceptionHandler;
    }
}

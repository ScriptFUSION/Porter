<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Connector\Recoverable\ExponentialSleepRecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * Specifies which resource to import, how it should be imported and how the data will be transformed.
 */
class ImportSpecification extends Specification
{
    private $resource;

    /**
     * Initializes this instance with the specified resource.
     *
     * @param ProviderResource $resource Resource.
     */
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
     * Gets the resource to import.
     *
     * @return ProviderResource Resource.
     */
    final public function getResource(): ProviderResource
    {
        return $this->resource;
    }

    /**
     * Adds the specified transformer to the end of the transformers queue.
     *
     * @param Transformer $transformer Transformer.
     *
     * @return $this
     */
    final public function addTransformer(Transformer $transformer): self
    {
        return parent::addAnyTransformer($transformer);
    }

    protected static function createDefaultRecoverableExceptionHandler(): RecoverableExceptionHandler
    {
        return new ExponentialSleepRecoverableExceptionHandler;
    }
}

<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Async\Throttle\NullThrottle;
use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Connector\Recoverable\ExponentialAsyncDelayRecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Transform\AsyncTransformer;

/**
 * Specifies which resource to import asynchronously, how it should be imported and how the data will be transformed.
 */
class AsyncImportSpecification extends Specification
{
    private $asyncResource;

    /** @var Throttle */
    private $throttle;

    /**
     * Initializes this instance with the specified asynchronous resource.
     *
     * @param AsyncResource $resource Asynchronous resource.
     */
    public function __construct(AsyncResource $resource)
    {
        $this->asyncResource = $resource;

        parent::__construct();
    }

    public function __clone()
    {
        $this->asyncResource = clone $this->asyncResource;
        // Throttle is not cloned because it most likely wants to be shared between imports.

        parent::__clone();
    }

    /**
     * Gets the asynchronous resource to import.
     *
     * @return AsyncResource Asynchronous resource.
     */
    final public function getAsyncResource(): AsyncResource
    {
        return $this->asyncResource;
    }

    final public function addTransformer(AsyncTransformer $transformer): self
    {
        return $this->addAnyTransformer($transformer);
    }

    protected static function createDefaultRecoverableExceptionHandler(): RecoverableExceptionHandler
    {
        return new ExponentialAsyncDelayRecoverableExceptionHandler;
    }

    /**
     * Gets the asynchronous connection throttle, invoked each time a connector fetches data.
     *
     * @return Throttle Asynchronous connection throttle.
     */
    final public function getThrottle(): Throttle
    {
        return $this->throttle ?? $this->throttle = new NullThrottle;
    }

    /**
     * Sets the asynchronous connection throttle, invoked each time a connector fetches data.
     *
     * @param Throttle $throttle Asynchronous connection throttle.
     *
     * @return $this
     */
    final public function setThrottle(Throttle $throttle): self
    {
        $this->throttle = $throttle;

        return $this;
    }
}

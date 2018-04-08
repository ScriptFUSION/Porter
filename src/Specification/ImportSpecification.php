<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Connector\FetchExceptionHandler\ExponentialSleepFetchExceptionHandler;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\FetchExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * Specifies which resource to import and how the data should be transformed.
 */
class ImportSpecification
{
    const DEFAULT_FETCH_ATTEMPTS = 5;

    /**
     * @var ProviderResource
     */
    private $resource;

    /**
     * @var string
     */
    private $providerName;

    /**
     * @var Transformer[]
     */
    private $transformers;

    /**
     * @var mixed
     */
    private $context;

    /**
     * @var bool
     */
    private $mustCache = false;

    /**
     * @var int
     */
    private $maxFetchAttempts = self::DEFAULT_FETCH_ATTEMPTS;

    /**
     * @var FetchExceptionHandler
     */
    private $fetchExceptionHandler;

    public function __construct(ProviderResource $resource)
    {
        $this->resource = $resource;

        $this->clearTransformers();
    }

    public function __clone()
    {
        $this->resource = clone $this->resource;

        $transformers = $this->transformers;
        $this->clearTransformers()->addTransformers(array_map(
            function (Transformer $transformer) {
                return clone $transformer;
            },
            $transformers
        ));

        is_object($this->context) && $this->context = clone $this->context;
        $this->fetchExceptionHandler && $this->fetchExceptionHandler = clone $this->fetchExceptionHandler;
    }

    /**
     * @return ProviderResource
     */
    final public function getResource()
    {
        return $this->resource;
    }

    /**
     * Gets the provider service name.
     *
     * @return string Provider name.
     */
    final public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * Sets the provider service name.
     *
     * @param string $providerName Provider name.
     *
     * @return $this
     */
    final public function setProviderName($providerName)
    {
        $this->providerName = "$providerName";

        return $this;
    }

    /**
     * Gets the ordered list of transformers.
     *
     * @return Transformer[]
     */
    final public function getTransformers()
    {
        return $this->transformers;
    }

    /**
     * Adds the specified transformer.
     *
     * @param Transformer $transformer Transformer.
     *
     * @return $this
     */
    final public function addTransformer(Transformer $transformer)
    {
        if ($this->hasTransformer($transformer)) {
            throw new DuplicateTransformerException('Transformer already added.');
        }

        $this->transformers[spl_object_hash($transformer)] = $transformer;

        return $this;
    }

    /**
     * Adds one or more transformers.
     *
     * @param Transformer[] $transformers Transformers.
     *
     * @return $this
     */
    final public function addTransformers(array $transformers)
    {
        foreach ($transformers as $transformer) {
            $this->addTransformer($transformer);
        }

        return $this;
    }

    /**
     * @return $this
     */
    final public function clearTransformers()
    {
        $this->transformers = [];

        return $this;
    }

    private function hasTransformer(Transformer $transformer)
    {
        return isset($this->transformers[spl_object_hash($transformer)]);
    }

    /**
     * @return mixed
     */
    final public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     *
     * @return $this
     */
    final public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return bool
     */
    final public function mustCache()
    {
        return $this->mustCache;
    }

    /**
     * @return $this
     */
    final public function enableCache()
    {
        $this->mustCache = true;

        return $this;
    }

    /**
     * @return $this
     */
    final public function disableCache()
    {
        $this->mustCache = false;

        return $this;
    }

    /**
     * Gets the maximum number of fetch attempts per connection.
     *
     * @return int Maximum fetch attempts.
     */
    final public function getMaxFetchAttempts()
    {
        return $this->maxFetchAttempts;
    }

    /**
     * Sets the maximum number of fetch attempts per connection before failure is considered permanent.
     *
     * @param int $attempts Maximum fetch attempts.
     *
     * @return $this
     */
    final public function setMaxFetchAttempts($attempts)
    {
        if (!is_int($attempts) || $attempts < 1) {
            throw new \InvalidArgumentException('Fetch attempts must be greater than or equal to 1.');
        }

        $this->maxFetchAttempts = $attempts;

        return $this;
    }

    /**
     * Gets the exception handler invoked each time a fetch attempt fails.
     *
     * @return FetchExceptionHandler Fetch exception handler.
     */
    final public function getFetchExceptionHandler()
    {
        return $this->fetchExceptionHandler ?: $this->fetchExceptionHandler = new ExponentialSleepFetchExceptionHandler;
    }

    /**
     * Sets the exception handler invoked each time a fetch attempt fails.
     *
     * @param FetchExceptionHandler $fetchExceptionHandler Fetch exception handler.
     *
     * @return $this
     */
    final public function setFetchExceptionHandler(FetchExceptionHandler $fetchExceptionHandler)
    {
        $this->fetchExceptionHandler = $fetchExceptionHandler;

        return $this;
    }
}

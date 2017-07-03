<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Transform\Transformer;
use ScriptFUSION\Retry\ExceptionHandler\ExponentialBackoffExceptionHandler;

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
    private $providerTag;

    /**
     * @var Transformer[]
     */
    private $transformers;

    /**
     * @var mixed
     */
    private $context;

    /**
     * @var CacheAdvice|null
     */
    private $cacheAdvice;

    /**
     * @var CacheAdvice
     */
    private $defaultCacheAdvice;

    /**
     * @var int
     */
    private $maxFetchAttempts = self::DEFAULT_FETCH_ATTEMPTS;

    /**
     * @var callable
     */
    private $fetchExceptionHandler;

    public function __construct(ProviderResource $resource)
    {
        $this->resource = $resource;

        $this->clearTransformers();
        $this->defaultCacheAdvice = CacheAdvice::SHOULD_NOT_CACHE();
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
        is_object($this->fetchExceptionHandler) && $this->fetchExceptionHandler = clone $this->fetchExceptionHandler;
    }

    /**
     * @return ProviderResource
     */
    final public function getResource()
    {
        return $this->resource;
    }

    /**
     * Gets the provider identifier tag.
     *
     * @return string Provider tag.
     */
    final public function getProviderTag()
    {
        return $this->providerTag;
    }

    /**
     * Sets the provider identifier tag.
     *
     * @param string $tag Provider tag.
     *
     * @return $this
     */
    final public function setProviderTag($tag)
    {
        $this->providerTag = "$tag";

        return $this;
    }

    /**
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
     * @return CacheAdvice
     */
    final public function getCacheAdvice()
    {
        return $this->cacheAdvice ?: $this->defaultCacheAdvice;
    }

    /**
     * @param CacheAdvice $cacheAdvice
     *
     * @return $this
     */
    final public function setCacheAdvice(CacheAdvice $cacheAdvice)
    {
        $this->cacheAdvice = $cacheAdvice;

        return $this;
    }

    /**
     * Gets the maximum number of fetch attempts per import.
     *
     * @return int Maximum fetch attempts.
     */
    final public function getMaxFetchAttempts()
    {
        return $this->maxFetchAttempts;
    }

    /**
     * Sets the maximum number of fetch attempts per import.
     *
     * @param int $attempts Maximum fetch attempts.
     *
     * @return $this
     */
    final public function setMaxFetchAttempts($attempts)
    {
        $this->maxFetchAttempts = max(1, $attempts | 0);

        return $this;
    }

    /**
     * Gets the exception handler invoked each time a fetch attempt fails.
     *
     * @return callable Exception handler.
     */
    final public function getFetchExceptionHandler()
    {
        return $this->fetchExceptionHandler ?: $this->fetchExceptionHandler = new ExponentialBackoffExceptionHandler;
    }

    /**
     * Sets the exception handler invoked each time a fetch attempt fails.
     *
     * @param callable $fetchExceptionHandler Exception handler.
     *
     * @return $this
     */
    final public function setFetchExceptionHandler(callable $fetchExceptionHandler)
    {
        $this->fetchExceptionHandler = $fetchExceptionHandler;

        return $this;
    }
}

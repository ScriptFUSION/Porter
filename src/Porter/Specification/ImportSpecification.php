<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;

class ImportSpecification
{
    /** @var ProviderDataSource */
    private $dataSource;

    /** @var Mapping */
    private $mapping;

    /** @var mixed */
    private $context;

    /** @var callable */
    private $filter;

    /** @var CacheAdvice */
    private $cacheAdvice;

    public function __construct(ProviderDataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    public function __clone()
    {
        $this->dataSource = clone $this->dataSource;
        $this->mapping !== null && $this->mapping = clone $this->mapping;
        is_object($this->context) && $this->context = clone $this->context;
    }

    /**
     * @return ProviderDataSource
     */
    final public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @return Mapping
     */
    final public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param Mapping $mapping
     *
     * @return $this
     */
    final public function setMapping(Mapping $mapping = null)
    {
        $this->mapping = $mapping;

        return $this;
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
     * @return callable
     */
    final public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param callable $filter
     *
     * @return $this
     */
    final public function setFilter(callable $filter = null)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return CacheAdvice
     */
    final public function getCacheAdvice()
    {
        return $this->cacheAdvice;
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
}

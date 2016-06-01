<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\ObjectFinalizedException;
use ScriptFUSION\Porter\Provider\ProviderData;

class ImportSpecification
{
    private $finalized = false;

    /** @var ProviderData */
    private $providerData;

    /** @var Mapping */
    private $mapping;

    /** @var mixed */
    private $context;

    /** @var callable */
    private $filter;

    public function __construct(ProviderData $providerData)
    {
        $this->providerData = $providerData;
    }

    /**
     * Finalizes this specification so no more changes can be written to it.
     *
     * @return $this
     */
    final public function finalize()
    {
        $this->finalized = true;

        return $this;
    }

    /**
     * @return bool
     */
    final public function isFinalized()
    {
        return $this->finalized;
    }

    private function failIfFinalized($function)
    {
        if ($this->isFinalized()) {
            throw new ObjectFinalizedException("Cannot \"$function\": specification is final.");
        }
    }

    /**
     * @return ProviderData
     */
    final public function getProviderData()
    {
        return $this->providerData;
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
    final public function setMapping(Mapping $mapping)
    {
        $this->failIfFinalized(__FUNCTION__);

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
        $this->failIfFinalized(__FUNCTION__);

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
    final public function setFilter(callable $filter)
    {
        $this->failIfFinalized(__FUNCTION__);

        $this->filter = $filter;

        return $this;
    }
}

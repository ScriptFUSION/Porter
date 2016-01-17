<?php
namespace ScriptFUSION\Porter;

use ScriptFUSION\Porter\Mapping\Mapping;
use ScriptFUSION\Porter\Provider\ProviderDataType;

class ImportSpecification
{
    private $finalized = false;

    /** @var ProviderDataType */
    private $providerDataType;

    /** @var array */
    private $parameters;

    /** @var Mapping */
    private $mapping;

    /** @var mixed */
    private $context;

    /** @var callable */
    private $filter;

    public function __construct(ProviderDataType $dataType, array $parameters = [])
    {
        $this
            ->setProviderDataType($dataType)
            ->setParameters($parameters)
        ;
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
            throw new ObjectFinalizedException("Cannot \"$function\": object finalized.");
        }
    }

    /**
     * @return ProviderDataType
     */
    final public function getProviderDataType()
    {
        return $this->providerDataType;
    }

    /**
     * @param ProviderDataType $providerDataType
     *
     * @return $this
     */
    final public function setProviderDataType(ProviderDataType $providerDataType)
    {
        $this->failIfFinalized(__FUNCTION__);

        $this->providerDataType = $providerDataType;

        return $this;
    }

    /**
     * @return array
     */
    final public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    final public function setParameters(array $parameters)
    {
        $this->failIfFinalized(__FUNCTION__);

        $this->parameters = $parameters;

        return $this;
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

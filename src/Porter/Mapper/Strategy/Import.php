<?php
namespace ScriptFUSION\Porter\Mapper\Strategy;

use ScriptFUSION\Mapper\MapperAware;
use ScriptFUSION\Mapper\MapperAwareTrait;
use ScriptFUSION\Mapper\Strategy\Strategy;
use ScriptFUSION\Porter\Mapper\ProviderDataMapping;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\PorterAwareTrait;
use ScriptFUSION\Porter\Specification\ImportSpecification;

class Import implements Strategy, MapperAware, PorterAware
{
    use MapperAwareTrait, PorterAwareTrait;

    private $specification;

    private $providerDataMapping;

    public function __construct(ImportSpecification $specification)
    {
        $this->specification = $specification;
    }

    public function __invoke($data, $context = null)
    {
        $specification = clone $this->specification;

        if (!$specification->isFinalized()) {
            $specification->setContext($context);
        }

        if ($this->providerDataMapping) {
            $providerData = $specification->getProviderData();

            foreach ($this->getMapper()->map($data, $this->providerDataMapping, $context) as $method => $value) {
                if (!method_exists($providerData, $method)) {
                    throw new \RuntimeException( // TODO. Proper exception type.
                        sprintf('No such method: %s::%s.', get_class($providerData), $method)
                    );
                }

                $providerData->$method($value);
            }
        }

        $generator = $this->getPorter()->import($specification);

        if ($generator->valid()) {
            return iterator_to_array($generator);
        }
    }

    /**
     * @param ProviderDataMapping $providerDataMapping
     *
     * @return $this
     */
    public function setProviderDataMapping(ProviderDataMapping $providerDataMapping)
    {
        $this->providerDataMapping = $providerDataMapping;

        return $this;
    }
}

<?php
namespace ScriptFUSION\Porter\Mapper\Strategy;

use ScriptFUSION\Mapper\MapperAware;
use ScriptFUSION\Mapper\MapperAwareTrait;
use ScriptFUSION\Mapper\Strategy\Strategy;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\PorterAwareTrait;
use ScriptFUSION\Porter\Specification\ImportSpecification;

class Import implements Strategy, MapperAware, PorterAware
{
    use MapperAwareTrait, PorterAwareTrait;

    private $specification;

    private $preImportCallbacks = [];

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

        foreach ($this->preImportCallbacks as $callback) {
            $callback($specification, $data, $context);
        }

        $generator = $this->getPorter()->import($specification);

        if ($generator->valid()) {
            return iterator_to_array($generator);
        }
    }

    public function addPreImportCallback(callable $callback)
    {
        $this->preImportCallbacks[] = $callback;

        return $this;
    }
}

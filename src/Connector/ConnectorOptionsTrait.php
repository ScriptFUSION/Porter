<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

trait ConnectorOptionsTrait
{
    /**
     * @var EncapsulatedOptions
     */
    private $options;

    /**
     * @return EncapsulatedOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function __clone()
    {
        $this->options = clone $this->options;
    }
}

<?php
namespace ScriptFUSION\Porter;

trait PorterAwareTrait
{
    /** @var Porter */
    private $porter;

    protected function getPorter()
    {
        return $this->porter;
    }

    public function setPorter(Porter $porter)
    {
        $this->porter = $porter;

        return $this;
    }
}
